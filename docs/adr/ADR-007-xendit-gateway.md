# ADR-007: Move the payment gateway from Midtrans to Xendit

- **Status**: Accepted
- **Date**: 2026-11-01
- **Deciders**: Project owner

---

## Context and Problem Statement
The owner asked to replace Midtrans (Snap popup) with Xendit. The gateway is in production but has handled no real
money yet, so the switch carries no financial migration risk.

---

## Decision
| Topic | Decision |
|---|---|
| Product | Xendit Invoice API (hosted payment page). Users are redirected to `invoice_url`. |
| Client | Plain `Http` facade with Basic auth (secret key as username). No SDK, so tests use `Http::fake()`. |
| Webhook | `POST /xendit/callback`, verified by the `x-callback-token` header (`hash_equals`), then invoice id and amount are matched against the order. |
| Status mapping | `PAID`/`SETTLED` = paid, `EXPIRED` = failed, anything else = ignored. |
| Expiry | `invoice_duration` = `Coupon::RESERVATION_HOURS * 3600`, keeping coupon reservation consistent. |
| Fallback | `orders:sync-pending` and the success page query `GET /v2/invoices/{id}` (server to server). |
| Schema | New nullable `orders.xendit_invoice_id` and `orders.payment_url`. `snap_token` stays as history. |
| Settlement | `OrderSettlementService::applyGatewayStatus()` takes a gateway-neutral `['status', 'amount']` array. |

---

## Consequences
- Midtrans package, config, service, and webhook route are removed.
- Pending paid Midtrans orders are set to `cancelled` by the data migration
  `2026_11_01_100100_cancel_legacy_midtrans_pending_orders`, so they no longer show a "Bayar" button or hold coupon
  quota. As a safety net, the checkout page still tells a buyer without `payment_url` to order again, and
  `orders:sync-pending` skips orders without `xendit_invoice_id`.
- Before deploying, confirm no Midtrans transaction is still payable, because `/midtrans/notification` is gone.
- Test/live mode follows the secret key type (`xnd_development_` vs `xnd_production_`); there is no `is_production` flag.
- No multi-gateway abstraction was added; a future gateway change is another service swap.

---

## Addendum 2026-11-13: shared account with FlowPlay
xenPlatform was rejected by Xendit, so FlowPlay uses this same Xendit account. Xendit allows one "Invoice paid"
callback URL per account, so it stays pointed at `/xendit/callback`. After the token check, a callback whose
`external_id` starts with `XENDIT_FLOWPLAY_EXTERNAL_ID_PREFIX` (default `sub_`) is forwarded unchanged (raw body and
`x-callback-token`) to `XENDIT_FLOWPLAY_CALLBACK_URL` instead of being matched to an order. Upstream 2xx answers 200,
upstream 4xx is passed through, upstream 3xx (redirects are not followed, so the body and token never go
elsewhere), 5xx or a connection error answers 502; every non-2xx makes Xendit retry. An empty URL turns
the relay off. Both apps share the callback token; FlowPlay uses its own Invoice-only API key.
