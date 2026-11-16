# Direct Bluetooth Printing (Portable 58mm Printers)

> **Status: removed from the app (2026-11-15).** The Bluetooth buttons and
> the auto-print-to-saved-printer flow were taken out of every screen at the
> owner's request. Printing now uses the normal tap-to-print dialog flow
> again (`public/js/mobile-print.js`). Everything below is kept, unwired, in
> case the feature is wanted later — to re-enable it, load
> `public/js/bluetooth-print.js` in `app/Views/layouts/admin.php` and add the
> Bluetooth button back to the ticket/receipt views.

How the field phones printed a TCT / receipt straight to a portable thermal
printer over Bluetooth — no print-service app, no print dialog, one tap.

## How it works

1. The officer taps **Bluetooth Print** (or the phone's print sheet offers it
   after saving a ticket).
2. `public/js/bluetooth-print.js` renders the on-screen ticket card with
   html2canvas. The capture briefly reuses the view's own `@media print`
   rules (the "58mm roll" layout), scales the card's *content* onto the 384
   dots a 58mm print head can reach, crops the nominal side padding and
   converts the result into a 1-bit bitmap (the `GS v 0` raster command).
3. The bytes go to the printer over Bluetooth LE (Web Bluetooth API), in
   small chunks, with a progress overlay. Ticket graphics — the seal, the
   ticked violation boxes, signatures — print exactly as drawn on screen.
4. The printer is remembered per browser. Every later ticket prints with no
   chooser: after "Save & Print Ticket" the redirect lands in print mode and
   the ticket goes straight to the saved printer (`autoBluetooth` flow).

Everything is client-side; the server is untouched.

## The one deployment requirement: HTTPS

Web Bluetooth only exists in a **secure context**. That means:

- ✅ `https://…` with a trusted certificate, or `http://localhost`
- ❌ `http://192.168.1.x/…` (the usual field setup) — `navigator.bluetooth`
  is simply `undefined` there, and no Bluetooth button can work.

For field use, expose the app over HTTPS. Cheapest options:

- **Cloudflare Tunnel** (`cloudflared tunnel --url http://localhost`) — free,
  gives a trusted HTTPS URL for the XAMPP box.
- **ngrok / localhost.run** — same idea.
- A real certificate on the server (Let's Encrypt) + DNS name on the LAN.

For quick testing on one phone, Chrome can be told to trust a LAN origin:
`chrome://flags/#unsafely-treat-insecure-origin-as-secure` → add
`http://192.168.x.x`. Test-only; do not ship this as the setup.

If the page is opened over plain HTTP the print sheet hides the Bluetooth
action and explains this; the normal Print buttons still work.

## Device support

| Device / browser                | Direct Bluetooth | Fallback                        |
| ------------------------------- | ---------------- | ------------------------------- |
| Android — Chrome / Edge         | ✅ Web Bluetooth | RawBT app (Classic SPP models)  |
| iPhone / iPad — Safari          | ❌ not supported | Print dialog / AirPrint, or the free **Bluefy** browser (supports Web Bluetooth) |
| iPhone — Bluefy browser         | ✅               | —                               |
| Desktop Chrome (testing)        | ✅               | —                               |

Printer side: any **BLE ESC/POS** 58mm printer. Known-good families include
Goojprt (PT-210, PT-260), Xprinter XP-P323B/P58, Phomemo/Peripage, Rongta RPP
series and the many clones that expose the `ff00` / `18f0` Bluetooth
services. Older **Classic Bluetooth (SPP-only)** models cannot be reached by
any browser — on Android the built-in **RawBT** fallback hands the same
ESC/POS bytes to the RawBT print-service app, which talks to SPP printers.

## Testing without a printer

- **Encoder tests** (byte-exact raster/ESC-POS checks, no browser needed):
  `node tests/js/bluetooth-print.test.js`
- **Test bench page**: open `/public/bt-print-test.html` (or
  `?url=officer/view/123?print=1`) in the phone's browser while signed in.
  It loads a real ticket, renders the exact bitmap, shows its size, lets you
  download the raw `.bin` for a printer app, and can send it over Bluetooth.

## Troubleshooting

| Symptom | Cause / fix |
| --- | --- |
| No Bluetooth button on the phone | Page not on HTTPS (see above), or the browser is Safari — use Chrome (Android) or Bluefy (iOS). |
| Printer not in the chooser | Printer is off, already connected to another phone, or Classic-SPP only. Wake it, disconnect the other phone, or use RawBT (Android). |
| Chooser opens but ticket never prints | The printer accepted the connection but not the data — retry; if it persists, try RawBT to check the printer itself. |
| Printout garbled / stops halfway | Weak signal or a printer with a tiny buffer; move closer and retry. The writer pauses between 180–256-byte chunks on purpose. |
| Wrong printer saves as default | Open the print sheet and use the failure dialog's "Forget saved printer", then pair again (or clear the `traffic.btPrinter` key in site data). |
| Output too small / off-centre | Check the test bench page: the raster must come out `384 · width ✓`. If not, the view's print CSS changed. |

## Files

- `public/js/bluetooth-print.js` — capture → raster → ESC/POS → BLE engine
  (**dormant**: not loaded by any page since 2026-11-15).
- `public/vendor/html2canvas/html2canvas.min.js` — DOM → canvas renderer
  (vendored; only used by the dormant engine).
- `public/bt-print-test.html` — standalone field debugging tool for the
  engine (still works when opened directly).
- `tests/js/bluetooth-print.test.js` — Node checks for the byte encoder
  (`node tests/js/bluetooth-print.test.js`).
- `public/js/mobile-print.js` — the live print helper: tap-to-print sheet on
  touch devices, auto-print on desktop, owns the post-print return trip.
