<?php
require_once("guiconfig.inc");

// Default to 'C' unless explicitly set to 'F'
$temp_unit = (isset($_COOKIE['was110_temp_unit']) && $_COOKIE['was110_temp_unit'] === 'F') ? 'F' : 'C';
?>

<table class="table table-striped table-hover">
    <tbody id="was110-tbody">
        <tr><td class="text-center">Loading metrics...</td></tr>
    </tbody>
</table>

<div style="text-align: right; padding: 5px 10px; border-top: 1px solid #ddd;">
    <strong style="margin-right: 10px;">Temperature:</strong>
    <label style="cursor: pointer; margin-right: 10px;">
        <input type="radio" name="was110_unit" value="C" onclick="setTempUnit('C')" <?= $temp_unit === 'C' ? 'checked' : '' ?>> &deg;C
    </label>
    <label style="cursor: pointer;">
        <input type="radio" name="was110_unit" value="F" onclick="setTempUnit('F')" <?= $temp_unit === 'F' ? 'checked' : '' ?>> &deg;F
    </label>
</div>

<script type="text/javascript">
//<![CDATA[
    function setTempUnit(unit) {
        document.cookie = "was110_temp_unit=" + unit + "; path=/; max-age=" + (60*60*24*365);
        update_was110_metrics();
    }

    function update_was110_metrics() {
        fetch('/widgets/widgets/was110.ajax.php')
            .then(function(response) {
                return response.text();
            })
            .then(function(data) {
                document.getElementById('was110-tbody').innerHTML = data;
            })
            .catch(function(error) {
                console.error('Error fetching WAS-110 metrics:', error);
            });
    }
    
    update_was110_metrics();
    setInterval(update_was110_metrics, 5000);
//]]>
</script>
