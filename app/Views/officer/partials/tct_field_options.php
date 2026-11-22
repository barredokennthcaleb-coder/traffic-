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
    <!-- Place options are the DETAIL entries on the CTRAMO Kabankalan
         assigned-area duty postings (Mon–Fri, Sat, Sun and the 24/7 detail).
         They are kept as printed so the ticket's Place matches the officer's
         assigned-area sheet. The field stays free-text for anything else. -->
    <!-- Monday to Friday detail -->
    <option value="City Mall / G-Mall &amp; Fenez Avenue"></option>
    <option value="Ceres / Col. Health / City Mall"></option>
    <option value="Shell, Cor. Nolod"></option>
    <option value="ICC Gimenez Blanco Pharmacy"></option>
    <option value="LGU-Guimbangon (Main)"></option>
    <option value="Gasoline / Lights"></option>
    <option value="Bingkingan"></option>
    <option value="Broadway / M&amp;S"></option>
    <option value="Bread Box (Main)"></option>
    <option value="Groceries / Meat Sec."></option>
    <option value="Unloading"></option>
    <option value="Tagula"></option>
    <option value="G-Mall / Crock Stock Farm"></option>
    <option value="Rivision / Nolod"></option>
    <option value="By-Pass / Shell Station"></option>
    <option value="B-Ri / Kab."></option>
    <option value="District Hospital"></option>
    <option value="Patrol / 24/7"></option>
    <option value="City Hall"></option>
    <option value="Public Plaza / Cathedral Church, Brgy. 6, Cross Street"></option>
    <!-- Saturday / Sunday detail -->
    <option value="E-Bus"></option>
    <option value="Ceres Terminal"></option>
    <option value="Cagayan Central College"></option>
    <option value="Tire Bums"></option>
    <option value="Traffic Lights"></option>
    <option value="Bread Box"></option>
    <option value="Loading / Unloading"></option>
    <option value="G-Mall / Stockfarm"></option>
    <option value="Rounding"></option>
    <option value="Radio Operator"></option>
    <!-- 24/7 post detail -->
    <option value="Ceres"></option>
    <option value="Bus"></option>
</datalist>
