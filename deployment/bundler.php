#!/usr/bin/php
<?php

require_once('../frontend/rabbit/path.inc');
require_once('../frontend/rabbit/get_host_info.inc');
require_once('../frontend/rabbit/rabbitMQLib.inc');

$client = new rabbitMQClient("deployServerMQ.ini","deploymentServer");


function getBundleInfo(array $extra = NULL)
{
    $machine;
    $machine = parse_ini_file("../frontend/rabbit/host.ini",$process_sections=true);
    if ($extra != NULL)
    {
        foreach ($extra as $ini)
        {
            $parsed = parse_ini_file($ini,true);
            if ($parsed)
            {
                $machine = array_merge($machine,$parsed);
            }
        }
    }
    return $machine;
}


$bundle = getBundleInfo(array("./bundles.ini"));
$requestType = $argv[1];

if($requestType == "addBundle"){
    $bundleName = $argv[2];

    $bundlePath = $bundle[$bundleName]["BUNDLE_PATH"];
    $bundleMachine = $bundle[$bundleName]["BUNDLE_MACHINE"];

    $testRequest = ["type" => "addBundle", "bundleName" => $bundleName, "bundlePath" => $bundlePath, "bundleMachine" => $bundleMachine];
    $response = $client->send_request($testRequest);

    $bundleData = json_decode($response, true);

    if(isset($bundleData["current_version"])){
        $bundleVersion = $bundleData["current_version"];
        echo "Creating bundle $bundleName, version $bundleVersion...\n";
        exec("scp -r $bundlePath kversola@172.30.89.183:/home/kversola/git/rabbitmqphp_example/bundles/$bundleName-v$bundleVersion");
    }
} else if($requestType == "rollout" || $requestType == "rollback"){
    $bundleName = $argv[2];
    $cluster = $argv[3];

    $testRequest = ["type" => $requestType, "bundleName" => $bundleName, "cluster" => $cluster];
    $response = $client->send_request($testRequest);


} else if($requestType == "confirmStatus") {
    $bundleName = $argv[2];
    $bundleVersion = $argv[3];
    $status = $argv[4];

    $testRequest = ["type" => "confirmStatus", "bundleName" => $bundleName, "version" => $bundleVersion, "status" => $status];
    $response = $client->send_request($testRequest);
    
}


# Create client to deployment vm

# parameters: --bundlename command (deploy [QA | prod], bundle) 

# bundle
# 1. Create take bundle data from ini
# 2. send bundle request to deployment server
# 3. send files through scp
# scp -r /home/git/IT490-Project/frontend/app kversola@172.30.89.183:/home/kversola/git/rabbitmqphp_example/bundles/clientApp-v1 