<?php
require_once("guiconfig.inc");

// --- HARDWARE THRESHOLDS ---
// Temperature (Celsius)
define('TEMP_HIGH_ALARM', 90.00);
define('TEMP_HIGH_WARN', 85.00);
define('TEMP_LOW_WARN', -45.00);
define('TEMP_LOW_ALARM', -50.00);

// Tx Power (dBm)
define('TX_HIGH_ALARM', 8.16);
define('TX_HIGH_WARN', 8.16);
define('TX_LOW_WARN', 3.00);
define('TX_LOW_ALARM', 2.00);

// Rx Power (dBm)
define('RX_HIGH_ALARM', -7.00);
define('RX_HIGH_WARN', -8.00);
define('RX_LOW_WARN', -28.54);
define('RX_LOW_ALARM', -29.59);

// Module Voltage (V)
define('VOLT_HIGH_ALARM', 3.60);
define('VOLT_HIGH_WARN', 3.47);
define('VOLT_LOW_WARN', 3.13);
define('VOLT_LOW_ALARM', 3.00);

// Transmit Bias (mA)
define('BIAS_HIGH_ALARM', 60.00);
define('BIAS_HIGH_WARN', 55.00);
define('BIAS_LOW_WARN', 0.00);
define('BIAS_LOW_ALARM', 0.00);
// ---------------------------

$was110_ip = "192.168.11.1";
$url = "https://{$was110_ip}/cgi-bin/luci/8311/metrics";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$metrics = ($httpcode == 200 && $response) ? json_decode($response, true) : null;
$temp_unit = (isset($_COOKIE['was110_temp_unit']) && $_COOKIE['was110_temp_unit'] === 'C') ? 'C' : 'F';

$friendly_labels = [
    'cpu1_tempC'     => 'CPU 1 Temperature',
    'cpu2_tempC'     => 'CPU 2 Temperature',
    'module_voltage' => 'Module Voltage',
    'optic_tempC'    => 'Optic Temperature',
    'ploam_state'    => 'PLOAM State',
    'rx_power_dBm'   => 'Receive Power',
    'tx_bias_mA'     => 'Transmit Bias',
    'tx_power_dBm'   => 'Transmit Power'
];

$tooltips = [
    'cpu1_tempC'     => "Good: > " . TEMP_LOW_WARN . "°C to < " . TEMP_HIGH_WARN . "°C\nHigh: Warn >= " . TEMP_HIGH_WARN . "°C, Alarm >= " . TEMP_HIGH_ALARM . "°C\nLow: Warn <= " . TEMP_LOW_WARN . "°C, Alarm <= " . TEMP_LOW_ALARM . "°C",
    'cpu2_tempC'     => "Good: > " . TEMP_LOW_WARN . "°C to < " . TEMP_HIGH_WARN . "°C\nHigh: Warn >= " . TEMP_HIGH_WARN . "°C, Alarm >= " . TEMP_HIGH_ALARM . "°C\nLow: Warn <= " . TEMP_LOW_WARN . "°C, Alarm <= " . TEMP_LOW_ALARM . "°C",
    'optic_tempC'    => "Good: > " . TEMP_LOW_WARN . "°C to < " . TEMP_HIGH_WARN . "°C\nHigh: Warn >= " . TEMP_HIGH_WARN . "°C, Alarm >= " . TEMP_HIGH_ALARM . "°C\nLow: Warn <= " . TEMP_LOW_WARN . "°C, Alarm <= " . TEMP_LOW_ALARM . "°C",
    'rx_power_dBm'   => "Good: > " . RX_LOW_WARN . " dBm to < " . RX_HIGH_WARN . " dBm\nHigh: Warn >= " . RX_HIGH_WARN . " dBm, Alarm >= " . RX_HIGH_ALARM . " dBm\nLow: Warn <= " . RX_LOW_WARN . " dBm, Alarm <= " . RX_LOW_ALARM . " dBm",
    'tx_power_dBm'   => "Good: > " . TX_LOW_WARN . " dBm to < " . TX_HIGH_WARN . " dBm\nHigh: Alarm >= " . TX_HIGH_ALARM . " dBm\nLow: Warn <= " . TX_LOW_WARN . " dBm, Alarm <= " . TX_LOW_ALARM . " dBm",
    'module_voltage' => "Good: > " . VOLT_LOW_WARN . " V to < " . VOLT_HIGH_WARN . " V\nHigh: Warn >= " . VOLT_HIGH_WARN . " V, Alarm >= " . VOLT_HIGH_ALARM . " V\nLow: Warn <= " . VOLT_LOW_WARN . " V, Alarm <= " . VOLT_LOW_ALARM . " V",
    'tx_bias_mA'     => "Good: > " . BIAS_LOW_WARN . " mA to < " . BIAS_HIGH_WARN . " mA\nHigh: Warn >= " . BIAS_HIGH_WARN . " mA, Alarm >= " . BIAS_HIGH_ALARM . " mA\nLow: Alarm <= " . BIAS_LOW_ALARM . " mA",
    'ploam_state'    => "Good: 51\nAlarm: 6, 7, 71, 72, 11\nWarn: 5 and transitional states"
];

function get_ploam_state_text($state) {
    $map = [
        0  => 'Power-up state', 1  => 'Initial state', 11 => 'Off-sync state',
        12 => 'Profile learning state', 2  => 'Stand-by state', 23 => 'Serial number state',
        3  => 'Serial number state', 4  => 'Ranging state', 5  => 'Operation state',
        51 => 'Associated state (Up)', 52 => 'Pending state', 
        6  => 'Intermittent LOS state', 7  => 'Emergency stop state',
        71 => 'Emergency stop off-sync state', 72 => 'Emergency stop in-sync state',
        81 => 'Downstream tuning off-sync state', 82 => 'Downstream tuning profile learning state',
        9  => 'Upstream tuning state'
    ];
    return isset($map[$state]) ? $map[$state] : "Unknown State ({$state})";
}

function get_temp_health_symbol($val) {
    if ($val >= TEMP_HIGH_ALARM || $val <= TEMP_LOW_ALARM) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    if ($val >= TEMP_HIGH_WARN || $val <= TEMP_LOW_WARN) return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
    return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
}

function get_tx_health_symbol($val) {
    if ($val >= TX_HIGH_ALARM || $val <= TX_LOW_ALARM) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    if ($val <= TX_LOW_WARN) return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
    return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
}

function get_rx_health_symbol($val) {
    if ($val >= RX_HIGH_ALARM || $val <= RX_LOW_ALARM) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    if ($val >= RX_HIGH_WARN || $val <= RX_LOW_WARN) return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
    return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
}

function get_voltage_health_symbol($val) {
    if ($val >= VOLT_HIGH_ALARM || $val <= VOLT_LOW_ALARM) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    if ($val >= VOLT_HIGH_WARN || $val <= VOLT_LOW_WARN) return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
    return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
}

function get_bias_health_symbol($val) {
    if ($val >= BIAS_HIGH_ALARM || $val <= BIAS_LOW_ALARM) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    if ($val >= BIAS_HIGH_WARN || $val <= BIAS_LOW_WARN) return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
    return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
}

function get_ploam_health_symbol($state) {
    if ($state === 51) return '<i class="fa fa-fw fa-check-circle text-success"></i>'; 
    if (in_array($state, [6, 7, 71, 72, 11])) return '<i class="fa fa-fw fa-times-circle text-danger"></i>'; 
    return '<i class="fa fa-fw fa-exclamation-circle text-warning"></i>'; 
}

if ($metrics && is_array($metrics)) {
    $empty_icon = '<i class="fa fa-fw"></i>';
    
    foreach ($metrics as $key => $value) {
        echo "<tr>";
        
        $label = isset($friendly_labels[$key]) ? $friendly_labels[$key] : ucwords(str_replace('_', ' ', $key));
        
        if (isset($tooltips[$key])) {
            $tooltip_attr = ' title="' . htmlspecialchars($tooltips[$key]) . '" style="cursor: help; text-decoration: underline dotted #999;"';
            echo "<th{$tooltip_attr}>" . htmlspecialchars($label) . "</th>";
        } else {
            echo "<th>" . htmlspecialchars($label) . "</th>";
        }
        
        echo "<td>";
        if ($key === 'ploam_state') {
            $symbol = get_ploam_health_symbol($value);
            $state_text = get_ploam_state_text($value);
            echo "{$symbol} " . htmlspecialchars("{$value} - {$state_text}");
        } elseif (is_array($value)) {
            echo htmlspecialchars(json_encode($value));
        } else {
            if (stripos($key, 'tempC') !== false && is_numeric($value)) {
                $symbol = get_temp_health_symbol($value);
                if ($temp_unit === 'F') {
                    $f_val = ($value * 9/5) + 32;
                    echo "{$symbol} " . htmlspecialchars(number_format($f_val, 2)) . " &deg;F";
                } else {
                    echo "{$symbol} " . htmlspecialchars(number_format($value, 2)) . " &deg;C";
                }
            } elseif ($key === 'rx_power_dBm' && is_numeric($value)) {
                $symbol = get_rx_health_symbol($value);
                echo "{$symbol} " . htmlspecialchars(number_format($value, 2)) . " dBm";
            } elseif ($key === 'tx_power_dBm' && is_numeric($value)) {
                $symbol = get_tx_health_symbol($value);
                echo "{$symbol} " . htmlspecialchars(number_format($value, 2)) . " dBm";
            } elseif (stripos($key, 'voltage') !== false && is_numeric($value)) {
                $symbol = get_voltage_health_symbol($value);
                echo "{$symbol} " . htmlspecialchars(number_format($value, 2)) . ' V';
            } elseif (stripos($key, 'mA') !== false && is_numeric($value)) {
                $symbol = get_bias_health_symbol($value);
                echo "{$symbol} " . htmlspecialchars(number_format($value, 2)) . ' mA';
            } else {
                echo "{$empty_icon} " . htmlspecialchars($value);
            }
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan=\"2\" class=\"text-danger text-center\">Unable to retrieve metrics. Verify the WAS-110 is reachable at " . htmlspecialchars($was110_ip) . " on HTTPS.</td></tr>";
}
?>