<?php

require_once __DIR__.'/dependencies/autoload.php';
require_once __DIR__.'/redcap-etl/dependencies/autoload.php';

use IU\RedCapEtlModule\AdminConfig;
use IU\RedCapEtlModule\Configuration;

$error   = '';
$success = '';

$module = new \IU\RedCapEtlModule\RedCapEtlModule();

$adminConfig = $module->getAdminConfig();

$servers = $module->getServers();

$configurationNames = $module->getUserConfigurationNames();

$selfUrl   = $module->getUrl(basename(__FILE__));
$listUrl = $module->getUrl("index.php");

#-------------------------------------------
# Get the configuration name
#-------------------------------------------
$configName = $_POST['configName'];
if (empty($configName)) {
    $configName = $_GET['configName'];
    if (empty($configName)) {
        $configName = $_SESSION['configName'];
    }
}
if (!empty($configName)) {
    $_SESSION['configName'] = $configName;
}

if (!empty($configName)) {
    $configuration = $module->getConfiguration($configName);
    $properties = $configuration->getProperties();
}


#-------------------------
# Set the submit value
#-------------------------
$submitValue = '';
if (array_key_exists('submitValue', $_POST)) {
    $submitValue = $_POST['submitValue'];
}

if (strcasecmp($submitValue, 'Save') === 0) {
    # Saving the schedule values
    $server = $_POST['server'];
    $schedule = array();
    
    $schedule[0] = $_POST['Sunday'];
    $schedule[1] = $_POST['Monday'];
    $schedule[2] = $_POST['Tuesday'];
    $schedule[3] = $_POST['Wednesday'];
    $schedule[4] = $_POST['Thursday'];
    $schedule[5] = $_POST['Friday'];
    $schedule[6] = $_POST['Saturday'];
    
    if (empty($configName)) {
        $error = 'ERROR: No ETL configuration specified.';
    } elseif (!isset($configuration)) {
        $error = 'ERROR: No ETL configuration found for '.$configName.'.';
    } elseif (empty($server)) {
        $error = 'ERROR: No server specified.';
    } else {
        $module->setConfigSchedule($configName, $server, $schedule);
        $success = " Schedule saved.";
    }
} else {
    # Just displaying page
    $server   = $configuration->getProperty(Configuration::CRON_SERVER);
    $schedule = $configuration->getProperty(Configuration::CRON_SCHEDULE);
}

?>

<?php
#--------------------------------------------
# Include REDCap's project page header
#--------------------------------------------
ob_start();
include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
$buffer = ob_get_clean();
$cssFile = $module->getUrl('resources/redcap-etl.css');
$link = '<link href="'.$cssFile.'" rel="stylesheet" type="text/css" media="all">';
$buffer = str_replace('</head>', "    ".$link."\n</head>", $buffer);
echo $buffer;
?>

<div class="projhdr"> <!--h4 style="color:#800000;margin:0 0 10px;"> -->
<img style="margin-right: 7px;" src="<?php echo APP_PATH_IMAGES ?>database_table.png">REDCap-ETL
</div>


<?php
#------------------------------
# Display module tabs
#------------------------------
$module->renderUserTabs($selfUrl);
?>

<?php
#----------------------------
# Display error, if any
#----------------------------
if (!empty($error)) { ?>
<div class="red" style="margin:20px 0;font-weight:bold;">
    <img src="/redcap/redcap_v8.5.11/Resources/images/exclamation.png">
    <?php echo $error; ?>
    </div>
<?php } ?>

<?php
#-----------------------------------
# Display success message, if any
#-----------------------------------
if (!empty($success)) { ?>
<div align='center' class='darkgreen' style="margin: 20px 0;">
    <img src='/redcap/redcap_v8.5.11/Resources/images/accept.png'><?php echo $success;?>
</div>
<?php } ?>

<?php
#---------------------------------------
# Configuration selection form
#---------------------------------------
?>
<form action="<?php echo $selfUrl;?>" method="post" 
      style="padding: 4px; margin-bottom: 0px; border: 1px solid #ccc; background-color: #ccc;">
    <span style="font-weight: bold;">Configuration:</span>
    <select name="configName" onchange="this.form.submit()">
    <?php
    $values = $module->getUserConfigurationNames();
    array_unshift($values, '');
    foreach ($values as $value) {
        if (strcmp($value, $configName) === 0) {
            echo '<option value="'.$value.'" selected>'.$value."</option>\n";
        } else {
            echo '<option value="'.$value.'">'.$value."</option>\n";
        }
    }
    ?>
    </select>
</form>

<br />


<?php
#print '<pre>'; print_r($_POST); print '</pre>'."\n";
#print "SERVER: {$server} <br />\n";
?>


<?php
if ($adminConfig->getAllowCron()) {
?>

<script type="text/javascript">
// Change radio buttons so that a checked
// radio button that is clicked will be
// unchecked
$(function () {
    var val = -1;
    var vals = {};
    vals['Sunday'] = -1;
    vals['Monday'] = -1;
    vals['Tuesday'] = -1;
    vals['Wednesday'] = -1;
    vals['Thursday'] = -1;
    vals['Friday'] = -1;
    vals['Saturday'] = -1;
    vals['Week'] = -1;

    $('input:radio').click(function () {
        name = $(this).attr('name');
        //alert('value:' + $(this).val());
        //alert('name:' + $(this).attr('name'));
        if ($(this).val() == vals[name]) {
            $(this).prop('checked',false);
            vals[name] = -1;
        } else {
            $(this).prop('checked',true);
            vals[name] = $(this).val();
        }
});
});
</script>


        
<form action="<?php echo $selfUrl;?>" method="post" style="margin-top: 14px;">

<div style="margin-bottom: 12px;">
<span style="font-weight: bold">Server:</span>
<?php
echo '<select name="server">'."\n";
echo '<option value="'.$serverName.'" >'."</option>\n";
foreach ($servers as $serverName) {
    $selected = '';
    if ($serverName === $server) {
        $selected = 'selected';
    }
    echo '<option value="'.$serverName.'" '.$selected.'>'.$serverName."</option>\n";
}
echo "</select>\n";
?>
</div>

  <!-- <fieldset style="border: 2px solid #ccc; border-radius: 7px; padding: 7px;"> -->
  <!-- <legend style="font-weight: bold;">Schedule Automated Repeating Run</legend> -->

  <table class="cron-schedule">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <?php
        foreach (AdminConfig::DAY_LABELS as $key => $label) {
            echo "<th class=\"day\">{$label}</th>\n";
        }
        ?>
      </tr>
    </thead>
    <tbody>
    <?php
    $row = 1;
    foreach ($adminConfig->getTimes() as $time) {
        if ($row % 2 === 0) {
            echo '<tr class="even-row">';
        } else {
            echo '<tr>';
        }
        echo "<td>".($adminConfig->getTimeLabel($time))."</td>";
        foreach (AdminConfig::DAY_LABELS as $day => $label) {
            $radioName = $label;
            $value = $time;
            
            $checked = '';
            if (isset($schedule[$day]) && $schedule[$day] == $value) {
                $checked = ' checked ';
            }

            if ($adminConfig->isAllowedCronTime($day, $time)) {
                echo '<td class="day" >';
                echo '<input type="radio" name="'.$radioName.'" value="'.$value.'" '.$checked.'>';
                echo '</td>'."\n";
            } else {
                echo '<td class="day" ><input type="radio" name="'.$radioName.'"'
                    .' value="'.$value.'" disabled></td>'."\n";
            }
        }
        echo "</tr>\n";
        $row++;
    }
    ?>
    </tbody>
  </table>
  <!-- </fieldset> -->
  <p>
    <input type="submit" name="submitValue" value="Save">
  </p>
</form>
<?php
}
?>

<?php include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php'; ?>

