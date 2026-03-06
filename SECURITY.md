# Security Considerations

This document describes known security considerations that have been reviewed and intentionally left unfixed, either because they require architectural changes, have very low practical risk, or are mitigated by other factors.

## Missing Server-to-Server Webhook Endpoint

**Context:** The entire module relies on browser redirects for payment finalization.
**Risk:** High (architectural)

The easyCredit module does not implement a dedicated server-to-server webhook endpoint. The entire payment flow depends on the customer's browser redirect:

1. Customer is redirected to easyCredit
2. Customer approves the installment plan
3. easyCredit redirects customer back to the shop
4. Only then is the order finalized and confirmed at easyCredit

If the browser redirect fails (browser closed, network error, timeout), the payment is approved at easyCredit but not finalized in the shop. There is no asynchronous mechanism to recover from this.

**Mitigation:** Manual intervention via the easyCredit Trading API in the admin panel. Shop operators should periodically check for unfinalized easyCredit orders. Implementing a webhook endpoint would be a significant architectural addition requiring coordination with easyCredit's API capabilities.

## `|raw` on Payment Description (oscGetPaymentDescription)

**Files:** `views/twig/extensions/themes/default/page/checkout/order.html.twig` (OXID 7), `Application/views/page/checkout/inc/payment_easycreditinstallment.tpl` (OXID 6.5)
**Risk:** Low-Medium

The payment description is output with `|raw` because it intentionally contains HTML (formatted installment plan from easyCredit API). The HTML is constructed server-side in `oscGetPaymentDescription()` which concatenates easyCredit API data into HTML tags.

**Mitigation:** The easyCredit API data (`paymentPlanTxt`) is now escaped with `htmlspecialchars()` before being embedded in HTML (fixed in the latest security update). The `|raw` is still needed to render the legitimate HTML structure (paragraphs, formatting). The remaining risk is limited to the `oxpayments.oxlongdesc` database field, which is admin-controlled.

## Session-ID in Callback URLs

**File:** `src/Core/Helper/EasyCreditInitializeRequestBuilder.php`
**Risk:** Low-Medium

The OXID Session ID (`sid`) is included in callback URLs (`urlErfolg`, `urlAbbruch`, `urlAblehnung`) sent to easyCredit servers. This exposes the session ID to:
- easyCredit servers (third party)
- Browser history and referrer headers
- Proxy and webserver logs

**Mitigation:** The session ID is required by OXID to associate the returning customer with their session. Replacing it with a one-time callback token would require changes to the core session handling. The risk is limited to scenarios where easyCredit infrastructure is compromised.

## Double-Confirmation Prevention

**File:** `src/Core/Domain/EasyCreditOrder.php`
**Risk:** Low

No database lock or idempotency key prevents double-confirmation of an easyCredit order. Double-click or parallel requests from multiple tabs could theoretically cause two `confirmOrder()` calls to reach the easyCredit API simultaneously. The session storage clearance provides minimal but non-atomic protection.

**Mitigation:** easyCredit's API rejects duplicate confirmations for the same transaction, providing server-side protection. A database-level lock would add defense-in-depth.

## DOM-XSS via jQuery `.html()` on AJAX Responses

**File:** `views/twig/widget/easycredit/oxpseasycredit_examplecalculation.html.twig` (OXID 7), Smarty equivalent (OXID 6.5)
**Risk:** Low

AJAX responses from shop-internal widget controllers are inserted into the DOM via jQuery `.html()`. The responses are server-rendered HTML from trusted widget controllers.

**Mitigation:** The AJAX endpoints are internal shop controllers that render Twig/Smarty templates. Exploitation would require a separate vulnerability in the widget controller or cache poisoning. Using `.text()` is not feasible because the response intentionally contains HTML (calculation tables, formatting).

---

*Last updated: 2026-03-06*
