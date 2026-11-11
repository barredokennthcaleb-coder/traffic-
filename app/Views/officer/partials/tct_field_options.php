<?php
/**
 * Suggestion lists for the Vehicle and Place fields used by the citation
 * forms (the record form and the edit modal). They are kept as <datalist>
 * elements so the values live in one place; the combos on the page read
 * their options and show a list that can actually be opened on phones,
 * where the native datalist pickers cannot. Included by the citation ledger
 * and the violator record page.
 */
?>
<datalist id="vehicleTypeOptions">
    <!-- ── Commonly Used Tricycles (Kabankalan City / Philippines) ── -->
    <option value="Tricycle - Honda TMX 155"></option>
    <option value="Tricycle - Kawasaki Barako 175"></option>
    <option value="Tricycle - Bajaj 125"></option>
    <option value="Tricycle - Honda TMX 125 Alpha"></option>
    <option value="Tricycle - Honda XRM 125"></option>
    <option value="Tricycle - Yamaha YTX 125"></option>
    <option value="Tricycle - Kawasaki CT100B"></option>
    <option value="Tricycle - Suzuki Smash 115"></option>
    <option value="Tricycle - Suzuki GD110"></option>
    <option value="Tricycle - TVS Max / Apache"></option>
    <option value="Tricycle - Bajaj RE"></option>
    <option value="Tricycle"></option>
    <!-- ── Other Vehicle Types ── -->
    <option value="Motorcycle"></option>
    <option value="Car"></option>
    <option value="Van"></option>
    <option value="SUV"></option>
    <option value="Jeepney"></option>
    <option value="Bus"></option>
    <option value="Truck"></option>
    <option value="Mitsubishi L300"></option>
</datalist>

<datalist id="locationOptions">
    <option value="Ceres Terminal, Kabankalan City"></option>
    <option value="Biyarin, Kabankalan City"></option>
    <?php foreach (kabankalan_areas() as $brgy): ?>
    <option value="<?= esc($brgy) ?>, Kabankalan City"></option>
    <?php endforeach; ?>
</datalist>
