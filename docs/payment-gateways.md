# درگاه‌های پرداخت — Payment Gateway Requirements

Reference for the three gateways wired into Cattalya: what each one needs
before it will work, what it expects on the wire, and what it sends back.

Sources (verified against the official docs):

- ZarinPal — <https://www.zarinpal.com/docs/paymentGateway/>
- IDPay v1.1 — <https://idpay.ir/web-service/v1.1/>
- DigiPay UPG — <https://www.mydigipay.com/developers/docs/upg/>

---

## 1. Concepts shared by all three

### Currency

Orders store `total_amount` in **Tomans**. Every gateway API here expects
**Rials**. `Payment::amountInRials()` performs the single ×10 conversion; no
driver should convert again. This was previously a live bug — ZarinPal was
being sent `amount × 10` *together with* `currency: IRT`, which is a 10×
overcharge.

### The `reference`

Each `payments` row gets a unique `reference` (`CTL-{order}-{random}`, ≤50
chars) **before** the gateway is called. It is sent as:

| Gateway  | Field carrying the reference |
| -------- | ---------------------------- |
| ZarinPal | `metadata.order_id` + a `?ref=` query param we add to `callback_url` |
| IDPay    | `order_id` |
| DigiPay  | `providerId` |

Callbacks are unauthenticated, so the reference is how a callback is matched
to a payment. Everything else in the request is then checked against the
stored row.

### Verification is mandatory and time-boxed

All three gateways **auto-reverse** a payment that is never verified:

| Gateway  | Verify window |
| -------- | ------------- |
| ZarinPal | No hard limit, but unverified transactions are recoverable only via `unVerified.json` |
| IDPay    | **10 minutes** |
| DigiPay  | **10 minutes** |

If the buyer closes the browser during the redirect, the callback never fires.
Use the reconciliation helpers (`ZarinPalGateway::unverified()`,
`IDPayGateway::inquiry()`) from a scheduled command to catch those.

### Callback route

`GET|POST /payment/callback/{gateway}`

`POST` is required — DigiPay always posts `application/x-www-form-urlencoded`,
and IDPay does when the web service is set to "Post form". The route is
CSRF-exempt (see `bootstrap/app.php`); it authenticates the request against
the stored payment instead of a session token.

### Registered callback domain

ZarinPal (`-14`) and IDPay (`38`) both reject callbacks whose domain does not
match what is registered in their panel. `APP_URL` must be the real public
domain in production, and the callback host must be registered with each
gateway before go-live.

---

## 2. ZarinPal

### Credentials

| Env var | What it is | How to get it |
| ------- | ---------- | ------------- |
| `ZARINPAL_MERCHANT_ID` | 36-character UUID | ZarinPal panel → درگاه پرداخت |
| `ZARINPAL_SANDBOX` | `true` uses sandbox.zarinpal.com | — |
| `ZARINPAL_REFERRER_ID` | optional affiliate code | ZarinPal, optional |

In sandbox **any** 36-character string works as the merchant id. All three
URLs (request/StartPay/verify) must point at the same environment or you get
unexpected errors.

### Endpoints

| Purpose | Live | Sandbox |
| ------- | ---- | ------- |
| Request | `https://payment.zarinpal.com/pg/v4/payment/request.json` | `https://sandbox.zarinpal.com/pg/v4/payment/request.json` |
| Redirect | `https://payment.zarinpal.com/pg/StartPay/{authority}` | `https://sandbox.zarinpal.com/pg/StartPay/{authority}` |
| Verify | `https://payment.zarinpal.com/pg/v4/payment/verify.json` | `https://sandbox.zarinpal.com/pg/v4/payment/verify.json` |
| Unverified | `https://payment.zarinpal.com/pg/v4/payment/unVerified.json` | same host, sandbox |

### Request (`POST request.json`, JSON)

| Field | Req. | Notes |
| ----- | ---- | ----- |
| `merchant_id` | ✔ | 36 chars |
| `amount` | ✔ | Rials when `currency` is `IRR` |
| `currency` | — | `IRR` (Rial) or `IRT` (Toman). We send `IRR`. |
| `callback_url` | ✔ | domain must match the registered one |
| `description` | ✔ | |
| `metadata.mobile` / `.email` / `.order_id` | — | we send all three |
| `referrer_id` | — | affiliate code |

Response: `data.code = 100`, `data.authority`, `data.fee`, `data.fee_type`.
On failure `errors` is an object with `code` and `message`; on success it is
an empty array.

### Callback

Query string only: `Authority`, `Status` (`OK` \| `NOK`). ZarinPal echoes
nothing else of ours, which is why we append `?ref=` ourselves.

### Verify (`POST verify.json`, JSON)

`merchant_id`, `amount` (must equal the requested amount to the Rial, else
`-50`), `authority`.

Response: `data.code`, `data.ref_id` (the customer-facing tracking number),
`data.card_pan`, `data.card_hash`, `data.fee`.

- `100` — verified now.
- `101` — already verified. **Money is settled**; treat as success, don't
  double-count.

### Error codes

Full table lives in `ZarinPalGateway::ERRORS`. The ones you will actually
hit during setup:

| Code | Meaning |
| ---- | ------- |
| `-10` | IP or merchant code incorrect |
| `-11` | merchant code not active |
| `-14` | callback URL domain doesn't match the registered domain |
| `-15` | gateway suspended |
| `-50` | verify amount differs from the requested amount |
| `-53` | payment doesn't belong to this merchant code |
| `-54` | invalid authority |

---

## 3. IDPay

### Credentials

| Env var | What it is | How to get it |
| ------- | ---------- | ------------- |
| `IDPAY_API_KEY` | 36-character key | IDPay dashboard → وب‌سرویس‌های من → new web service |
| `IDPAY_SANDBOX` | sends `X-SANDBOX: 1` | — |

Sandbox is a **header flag on the production host**, not a separate host. In
sandbox, callback-domain and IP restrictions are not enforced.

Additional account requirements before live traffic works:

- The web service must be **approved** (error `14` if still under review).
- A **verified bank account** must be attached (errors `21` / `24`).
- The calling server IP must be listed on the web service (error `13`).
- The callback domain must match the registered one (error `38`).

### Endpoints

| Purpose | URL |
| ------- | --- |
| Create | `POST https://api.idpay.ir/v1.1/payment` |
| Verify | `POST https://api.idpay.ir/v1.1/payment/verify` |
| Inquiry | `POST https://api.idpay.ir/v1.1/payment/inquiry` |
| List | `POST https://api.idpay.ir/v1.1/payment/transactions` |

Headers on every call: `X-API-KEY`, `X-SANDBOX`, `Content-Type: application/json`.

### Create request

| Field | Req. | Notes |
| ----- | ---- | ----- |
| `order_id` | ✔ | ≤50 chars — our `reference` |
| `amount` | ✔ | **Rials, 1,000 – 500,000,000** |
| `callback` | ✔ | ≤2048 chars, registered domain |
| `name` / `phone` / `mail` / `desc` | — | ≤255 chars; invalid values are silently dropped |

**Success is HTTP `201 Created`, not `200`** — checking only for `200` will
make every payment look like a failure. Response: `{ id, link }`. Redirect the
buyer to `link`; store `id`.

### Callback

Sent as GET query string or POST form, per the dashboard setting
("روش بازگشت پس از پرداخت").

- GET: `status`, `track_id`, `id`, `order_id`
- POST: the above plus `amount`, `card_no`, `hashed_card_no`, `date`

**`status` on the callback is normally `10` — "awaiting confirmation".** This
is the success path, not a failure. The previous implementation only accepted
`100`/`101` here and therefore rejected every real payment. We accept
`10`, `100`, `101` as verifiable.

### Verify request

`{ id, order_id }`. Must be called within **10 minutes** or the buyer is
refunded (error `54` afterwards).

Response: `status`, `track_id`, `id`, `order_id`, `amount`, `date`,
`payment{ track_id, amount, card_no, hashed_card_no, date }`, `verify{ date }`.

Settled statuses: `100` (confirmed), `101` (already confirmed), `200`
(settled to the merchant).

### Double-spend

IDPay explicitly makes this the merchant's responsibility: a customer can save
an IDPay receipt and replay it. Our driver enforces it by requiring the
callback `id` to equal the stored `authority` and the verified `amount` to
equal the order amount, on top of the unique `reference`.

### Status codes

| Code | Meaning |
| ---- | ------- |
| 1 | پرداخت انجام نشده |
| 2 | پرداخت ناموفق |
| 3 | خطا رخ داده |
| 4 | بلوکه شده |
| 5 | برگشت به پرداخت‌کننده |
| 6 | برگشت خورده سیستمی |
| 7 | انصراف از پرداخت |
| 8 | به درگاه پرداخت منتقل شد |
| 10 | در انتظار تأیید پرداخت |
| 100 | پرداخت تأیید شده |
| 101 | پرداخت قبلاً تأیید شده |
| 200 | به دریافت‌کننده واریز شد |

Error codes are in `IDPayGateway::ERRORS` (`11`–`54`).

---

## 4. DigiPay (UPG)

The most involved of the three: OAuth2, four credentials, and a callback that
arrives as a POST.

### Credentials

All four are issued by **DigiPay merchant support** — there is no self-serve
panel and no single API key.

| Env var | Role |
| ------- | ---- |
| `DIGIPAY_CLIENT_ID` | combined into the HTTP Basic header |
| `DIGIPAY_CLIENT_SECRET` | combined into the HTTP Basic header |
| `DIGIPAY_USERNAME` | OAuth password-grant body |
| `DIGIPAY_PASSWORD` | OAuth password-grant body |
| `DIGIPAY_SANDBOX` | `true` → `uat.mydigipay.info` |
| `DIGIPAY_TICKET_TYPE` | `0` for the standard gateway |
| `DIGIPAY_AGENT` | `WEB` |

> The previous implementation used `grant_type=client_credentials` against
> `/tokens/generate` and posted to `/purchases`. None of those exist. The
> real flow is below.

### Base URLs

| Env | Base |
| --- | ---- |
| Sandbox (UAT) | `https://uat.mydigipay.info/digipay/api` |
| Live | `https://api.mydigipay.com/digipay/api` |

### Step 1 — OAuth token

```
POST {base}/oauth/token
Authorization: Basic base64(client_id:client_secret)
Content-Type: multipart/form-data

username=…&password=…&grant_type=password
```

The body **must be `multipart/form-data`** — not JSON, not urlencoded.

Response: `access_token`, `refresh_token`, `token_type`, `expires_in` (~3599s),
`scope`, `jti`. `401` means bad client credentials or bad username/password.

The token is cached for `expires_in − 60` seconds. A `refresh_token` grant
exists (`grant_type=refresh_token`) for long-lived processes; we simply
re-authenticate, which is simpler and equally correct for a web checkout.

### Step 2 — Purchase ticket

```
POST {base}/businesses/ticket?type=0
Authorization: Bearer {access_token}
Agent: WEB
Digipay-Version: 2022-02-02
Content-Type: application/json
```

| Field | Req. | Notes |
| ----- | ---- | ----- |
| `amount` | ✔ | Rials |
| `providerId` | ✔ | our unique per-transaction id — the **only** field we control that comes back |
| `redirectUrl` | ✔ | the payment result is POSTed here |
| `userType` | ✔ | `0` identified (needs `cellNumber`), `2` anonymous guest |
| `cellNumber` | conditional | required when `userType = 0` |

Response: `result{ status, message, level, title }`, `payUrl`, `ticket`.
`result.status = 0` means success. Redirect the buyer to `payUrl`.

**Ticket types** — `type=0` is the standard smart payment gateway (UPG).
Types `5`, `11`, `13` etc. are BNPL / credit / installment products with extra
required fields (`basketDetailsDto`) and extra lifecycle calls
(`/purchases/deliver`). Only `type=0` is implemented here.

Without a `cellNumber`, DigiPay shows the buyer IPG only — no wallet or credit
options.

### Step 3 — Callback

DigiPay **POSTs** `application/x-www-form-urlencoded` to `redirectUrl`:

```
result=SUCCESS&providerId=CTL-12-AB12CD34EF&trackingCode=15547930631614167567972&amount=1500000
```

| `result` | Meaning |
| -------- | ------- |
| `SUCCESS` | transaction succeeded |
| `FAILURE` | transaction error |
| `IPG_FAILURE` | IPG error |
| `CANCELED` | buyer cancelled |
| `INTERNAL_ERROR` | system error |
| `INVALID_TICKET` | invalid ticket / invalid payment state |

Note the tracking code is **not known at initiate time** — only the ticket is.
This is precisely why the payment row is matched by `providerId`, not by the
gateway's own identifier.

### Step 4 — Verify

```
POST {base}/purchases/verify/{trackingCode}
Authorization: Bearer {access_token}
```

No request body. Must happen within **10 minutes**.

Response: `result{ status }`, `trackingCode`, `providerId`, `terminalId`,
`rrn` (bank reference — this is the customer-facing tracking number),
`maskedPan`, `pspCode`, `pspName`, `amount`, `paymentGateway`.

If verify returns **`9011` ("outcome unknown")**, DigiPay's documentation asks
that verify be **retried** until the purchase reaches a definite state. Our
driver logs this as `retryable`; a retry job is a sensible follow-up.

### Payment gateway types returned in `paymentGateway`

`0` IPG · `1` DPG · `3` WALLET · `4` CPG (credit)

### Result codes

| Code | Meaning |
| ---- | ------- |
| 0 | success |
| 1054 | invalid input |
| 9000 | purchase not found |
| 9001 | invalid payment token |
| 9003 | purchase expired |
| 9004 | purchase in progress |
| 9005 | not payable |
| 9006 | gateway communication error |
| 9007 | purchase unsuccessful |
| 9008 | already registered with different data |
| 9009 | verify window expired |
| 9010 | verify failed |
| 9011 | verify outcome unknown — retry |
| 9012 | invalid purchase state |
| 9030 | cellNumber required for registered users |
| 9031 | ticket cannot be issued for this user |

### Other DigiPay services (not implemented)

`POST /reverse`, `POST /purchases/deliver?type={5,13}`, `POST /refunds`,
`POST /refunds/config`. Refunds require a certificate file from
`/refunds/config`.

---

## 5. Code map

| File | Role |
| ---- | ---- |
| `config/gateways.php` | credentials, endpoints, enable flags |
| `app/Services/Payment/Contracts/PaymentGateway.php` | driver interface |
| `app/Services/Payment/Gateways/AbstractGateway.php` | HTTP client, credential gating, log scrubbing |
| `app/Services/Payment/Gateways/*Gateway.php` | one per gateway, with its official code tables |
| `app/Services/Payment/PaymentService.php` | row creation, locking, idempotency, order transition |
| `app/Http/Controllers/PaymentController.php` | initiate + callback |

### Adding a gateway

1. Add a `drivers.{name}` block to `config/gateways.php`.
2. Extend `AbstractGateway`; implement `name()`, `requiredCredentials()`,
   `pay()`, `resolveReference()`, `verify()`.
3. Register it in `PaymentService::$drivers`.

The checkout selector, the order page and validation all read from
`PaymentService::gatewayOptions()`, so nothing else needs changing. The
`payments.gateway` column is a plain string — no enum migration needed.

---

## 6. Go-live checklist

- [ ] `APP_URL` is the real public HTTPS domain.
- [ ] Callback domain registered with every gateway in use.
- [ ] Server IP whitelisted (IDPay web service).
- [ ] `*_SANDBOX=false` and real credentials in place.
- [ ] Bank account verified and attached (IDPay), merchant active (ZarinPal).
- [ ] A queued reconciliation job covering lost callbacks inside each verify
      window (IDPay/DigiPay: 10 minutes).
- [ ] `PAYMENT_GATEWAY` default points at a gateway that is actually enabled.
