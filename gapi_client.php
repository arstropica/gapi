<?php

    // session_start for caching
    session_start();

    require 'gapi.php';

    $output = "";

    $profile = "";

    $guser = "";

    $gpass = "";

    $datestr = "";

    $dimensions = "";

    $metric = "";

    if ($_POST) {

        unset($_SESSION['auth']);
        
        unset($_SESSION['cache']);
        
        unset($_SESSION['profile']);
        
        $profile = @$_POST['profile'];

        $guser = @$_POST['guser'];

        $gpass = @$_POST['gpass'];  

        $datestr = @$_POST['dates'];

        $dates = empty($datestr) ? array(date('Y-m-d', strtotime("-1 month")), date('Y-m-d')) : array_map('trim', explode("::", str_replace(array("-", "/"), array("::", "-"), $datestr)));

        $dimensions = @$_POST['dimensions'];

        $metric = @$_POST['metric'];

        try {

            // construct the class
            $oAnalytics = new analytics($guser, $gpass);

            // set it up to use caching
            $oAnalytics->useCache();

            if (strpos($profile, "ga:") === 0)
                $oAnalytics->setProfileById($profile);
            else
                $oAnalytics->setProfileByName($profile);
            // $oAnalytics->setProfileByName('google.com');
            // or $oAnalytics->setProfileById('ga:123456');

            // set the date range
            $oAnalytics->setDateRange($dates[0], $dates[1]);
            // or $oAnalytics->setMonth(date('n'), date('Y'));
            // or $oAnalytics->setDateRange('YYYY-MM-DD', 'YYYY-MM-DD');

            $output .= '<pre>';
            // print out all Account Data
            $output .= "<h2>Available Accounts</h2>";
            $output .= print_r($oAnalytics->getAccounts(), true);

            // print out visitors for given period
            $output .= "<h2>Visitors (31 Days)</h2>";
            $output .= print_r($oAnalytics->getVisitors(), true);

            // print out pageviews for given period
            $output .= "<h2>Pageviews (31 Days)</h2>";
            $output .= print_r($oAnalytics->getPageviews(), true);

            // use dimensions and metrics for output
            // see: http://code.google.com/intl/nl/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html
            $output .= "<h2>Dimension: $dimensions ($metric)</h2>";
            $output .= print_r($oAnalytics->getData(array(   'dimensions' => $dimensions,
            'metrics'    => $metric,
            'sort'       => $dimensions)), true);

        } catch (Exception $e) { 
            $output .= 'Caught exception: ' . $e->getMessage(); 
        }           
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Lang" content="en">
        <title>Google Analytics HTTP Client Test</title>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/css/bootstrap.min.css" rel="stylesheet">

        <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>

        <link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/1.3.5/daterangepicker-bs2.css">

        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.7.0/moment.min.js"></script>

        <script src="//cdn.jsdelivr.net/bootstrap.daterangepicker/1.3.5/daterangepicker.js"></script>

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">

    </head>
    <body>
        <form class="form-horizontal" method="post">
            <fieldset>

                <!-- Form Name -->
                <legend>Google Analytics HTTP Client Test</legend>

                <!-- Text input-->
                <div class="control-group">
                    <label class="control-label" for="profile">Profile ID / Domain</label>
                    <div class="controls">
                        <input id="profile" name="profile" type="text" placeholder="ga:xxxx / domain.com" class="input-xlarge" required="" value="<?php echo $profile; ?>">

                    </div>
                </div>

                <!-- Text input-->
                <div class="control-group">
                    <label class="control-label" for="guser">Enter Email:</label>
                    <div class="controls">
                        <input id="guser" name="guser" type="text" placeholder="email address" class="input-xlarge" required="" value="<?php echo $guser; ?>">

                    </div>
                </div>

                <!-- Password input-->
                <div class="control-group">
                    <label class="control-label" for="gpass">Enter Password:</label>
                    <div class="controls">
                        <input id="gpass" name="gpass" type="password" placeholder="password" class="input-xlarge" required="" value="<?php echo $gpass; ?>">

                    </div>
                </div>

                <!-- Text input-->
                <div class="control-group">
                    <label class="control-label" for="dates">Date Range:</label>
                    <div class="controls">
                        <div class="input-prepend">
                            <span class="add-on"><i class="fa fa-calendar"></i></span><input id="dates" name="dates" type="text" placeholder="2012/01/01 - 2013/12/31" class="input-large" required="" value="<?php echo $datestr; ?>">
                        </div>

                    </div>
                </div>

                <!-- Select Basic -->
                <div class="control-group">
                    <label class="control-label" for="dimensions">Dimension:</label>
                    <div class="controls">
                        <select id="dimensions" name="dimensions" class="input-medium">
                            <option>ga:keyword</option>
                            <option>ga:referralPath</option>
                            <option>ga:fullReferrer</option>
                            <option>ga:source</option>
                            <option>ga:medium</option>
                        </select>
                    </div>
                </div>

                <!-- Select Basic -->
                <div class="control-group">
                    <label class="control-label" for="metric">Metric:</label>
                    <div class="controls">
                        <select id="metric" name="metric" class="input-medium">
                            <option>ga:visits</option>
                            <option>ga:sessions</option>
                            <option>ga:bounces</option>
                            <option>ga:avgTimeOnSite</option>
                        </select>
                    </div>
                </div>

                <!-- Button -->
                <div class="control-group">
                    <label class="control-label" for="submitbutton"></label>
                    <div class="controls">
                        <button id="submitbutton" name="submitbutton" class="btn btn-primary">Submit</button>
                    </div>
                </div>

            </fieldset>
        </form>
        <div style="border-bottom: 1px solid #e5e5e5;"><br /></div>
        <br>
        <?php echo $output; ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#dates').daterangepicker({format: 'YYYY/MM/DD'});
            });
        </script>        
    </body>
</html>
