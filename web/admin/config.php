<?php

/** @var \IU\RedCapEtlModule\RedCapEtlModule $module */

if (!SUPER_USER) {
    exit("Only super users can access this page!");
}

require_once __DIR__.'/../../dependencies/autoload.php';

use \IU\REDCapETL\Version;

use \IU\RedCapEtlModule\AdminConfig;
use \IU\RedCapEtlModule\Filter;
use \IU\RedCapEtlModule\RedCapEtlModule;

$selfUrl = $module->getUrl(RedCapEtlModule::ADMIN_HOME_PAGE);

$cronInfoUrl = $module->getUrl(RedCapEtlModule::CRON_DETAIL_PAGE);

$adminConfig = $module->getAdminConfig();

$submitValue = $_POST['submitValue'];

if (strcasecmp($submitValue, 'Save') === 0) {
    try {
        $adminConfig->set($_POST);
        
        $module->setAdminConfig($adminConfig);
        $success = "Admin configuration saved.";
    } catch (Exception $exception) {
        $error = 'ERROR: '.$exception->getMessage();
    }
}

?>

<?php #include APP_PATH_DOCROOT . 'ControlCenter/header.php'; ?>

<?php
#---------------------------------------------
# Include REDCap's control center page header
#---------------------------------------------
ob_start();
include APP_PATH_DOCROOT . 'ControlCenter/header.php';
$buffer = ob_get_clean();
$cssFile = $module->getUrl('resources/redcap-etl.css');
$link = '<link href="'.$cssFile.'" rel="stylesheet" type="text/css" media="all">';
$buffer = str_replace('</head>', "    ".$link."\n</head>", $buffer);
echo $buffer;
?>

<h4><img style="margin-right: 7px;" src="<?php echo APP_PATH_IMAGES ?>table_gear.png">REDCap-ETL Admin</h4>


<?php

$module->renderAdminPageContentHeader($selfUrl, $error, $warning, $success);

?>

<?php
#print "<pre>POST:\n"; print_r($_POST); print "</pre>\n";
?>

<form action="<?php echo $selfUrl;?>" method="post">

    <p>
    Version: <?php echo Filter::escapeForHtml($module->getVersion()); ?>
    </p>
    
    <?php
    #--------------------------------------------------------
    # SSL Certificate Verification
    #--------------------------------------------------------
    $checked = '';
    if ($adminConfig->getSslVerify()) {
        $checked = 'checked';
    }
    ?>
    <input type="checkbox" name="<?php echo AdminConfig::SSL_VERIFY;?>" <?php echo $checked;?> >
    SSL certificate verification
    <br />
    
    <?php
    #----------------------------------------------------
    # Allow Embedded Server
    #----------------------------------------------------
    $checked = '';
    if ($adminConfig->getAllowEmbeddedServer()) {
        $checked = 'checked';
    }
    ?>
    <input type="checkbox" name="<?php echo AdminConfig::ALLOW_EMBEDDED_SERVER;?>" <?php echo $checked;?>>
    Allow embedded REDCap-ETL server
    (<?php echo Version::RELEASE_NUMBER;?>)
    <br />
    
    <span  style="padding-left: 4em;">Embedded server e-mail from address: </span>
    <input type="text" name="<?php echo AdminConfig::EMBEDDED_SERVER_EMAIL_FROM_ADDRESS;?>" size="50"
        value="<?php echo Filter::escapeForHtmlAttribute($adminConfig->getEmbeddedServerEmailFromAddress());?>">
    <br />

    <span  style="padding-left: 4em;">Embedded server log file: </span>
    <input type="text" name="<?php echo AdminConfig::EMBEDDED_SERVER_LOG_FILE;?>" size="61"
        value="<?php echo Filter::escapeForHtmlAttribute($adminConfig->getEmbeddedServerLogFile());?>">
    <br />
        
    <?php
    #--------------------------------------------------
    # Allow On Demand
    #--------------------------------------------------
    $checked = '';
    if ($adminConfig->getAllowOnDemand()) {
        $checked = 'checked';
    }
    ?>
    <input type="checkbox" name="<?php echo AdminConfig::ALLOW_ON_DEMAND;?>" <?php echo $checked;?>>
    Allow ETL jobs to be run on demand? <br />
    
    <?php
    #------------------------------------------------
    # Allow Cron (Scheduled) Jobs
    #------------------------------------------------
    $checked = '';
    if ($adminConfig->getAllowCron()) {
        $checked = 'checked';
    }
    ?>
    <input type="checkbox" name="<?php echo AdminConfig::ALLOW_CRON;?>" <?php echo $checked;?>>
    Allow ETL cron jobs? <br />

    <p style="text-align: center; margin-top: 14px;">Allowed ETL cron job times
    and number of scheduled jobs per time</p>
    
    <table class="cron-schedule admin-cron-schedule">
      <thead>
        <tr>
          <th>&nbsp;</th>
            <?php
            foreach (AdminConfig::DAY_LABELS as $dayLabel) {
                echo '<th class="day">'.$dayLabel."</th>\n";
            }
            ?>
        </tr>
      </thead>
    <tbody>
        
    <?php
    #---------------------------------------------------
    # Allowed and schedule cron jobs
    #---------------------------------------------------
    $cronJobs = $module->getAllCronJobs();
    $row = 1;
    foreach (range(0, 23) as $time) {
        if ($row % 2 === 0) {
            echo '<tr class="even-row">'."\n";
        } else {
            echo '<tr>'."\n";
        }
        $row++;
        $label = $adminConfig->getHtmlTimeLabel($time);
    ?>
        <td class="time-range"><?php echo $label;?></td>
        
        <?php
        foreach (range(0, 6) as $day) {
            $name = AdminConfig::ALLOWED_CRON_TIMES.'['.$day.']['.$time.']';
            $count = count($cronJobs[$day][$time]);
            
            $jobsUrl = $cronInfoUrl.'&selectedDay='.$day.'&selectedTime='.$time;

            $checked = '';
            if ($adminConfig->isAllowedCronTime($day, $time)) {
                $checked = ' checked ';
            }
            echo '<td class="day" style="position: relative;">'."\n";
            echo '<input type="checkbox" name="'.$name.'" '.$checked.'>';
            if ($count > 0) {
                echo '<a href="'.$jobsUrl.'" style="position: absolute; top: 1px; right: 4px;">'.$count.'</a>';
            }
            echo '</td>'."\n";
        }
        ?>
      </tr>
    <?php
    }
    ?>
    </tbody>
  </table>
  <p>
    <input type="submit" name="submitValue" value="Save">
  </p>
</form>

<?php
#print "<pre>\n"; print_r($cronJobs); print "</pre>\n";
?>

<?php include APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
