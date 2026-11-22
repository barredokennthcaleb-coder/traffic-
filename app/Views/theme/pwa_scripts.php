<?php
/**
 * Progressive Web App behaviour. Loaded by the admin layout and the standalone
 * public screens; it registers the service worker and wires the install,
 * offline and update notices. Versioned by file timestamp so a cached copy can
 * never outlive a fix.
 */
$pwaJsVersion = is_file(FCPATH . 'js/pwa.js') ? (string) filemtime(FCPATH . 'js/pwa.js') : '1';
?>
<script src="<?= base_url('js/pwa.js') ?>?v=<?= $pwaJsVersion ?>" defer></script>
