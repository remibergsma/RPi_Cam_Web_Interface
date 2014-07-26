<?php
// Wrapper script to record photo/video with the RPi_Cam
// It makes sure there's max one thread at a time.
// Otherwise we seem to break stuff seriously :-s
//
// Copyright 2014, Remi Bergsma, github@remi.nl
// 
// Licensed to the Apache Software Foundation (ASF) under one
// or more contributor license agreements.  See the NOTICE file
// distributed with this work for additional information
// regarding copyright ownership.  The ASF licenses this file
// to you under the Apache License, Version 2.0 (the
// "License"); you may not use this file except in compliance
// with the License.  You may obtain a copy of the License at
//
//   http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing,
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
// KIND, either express or implied.  See the License for the
// specific language governing permissions and limitations
// under the License.

// lock file
$lockfile = "/var/www/_cameraLocked";

// exit if locked
if (file_exists($filename)) {
	exit("Locked! Halting.");
}

// set lock file
file_put_contents($lockfile, "LOCKED");

// check command
switch ($_GET['cmd']) {
	case "video":
		// default to 10 sec
		if ($_GET['seconds']) {
			$sleep = $_GET['seconds'];
		} else {
			$sleep = 10;
		}

		// record video
		$pipe = fopen("FIFO","w");
		fwrite($pipe, "ca 1");
		// record length
		sleep($sleep);
		fwrite($pipe, "ca 0");
		fclose($pipe);
		// video processing time
		sleep(floor($sleep/2));
		break;

	case "photo":
		// record photo
		$pipe = fopen("FIFO","w");
		fwrite($pipe, "im");
		fclose($pipe);
		break;

	case "photoseries":
		// default to 3
		if ($_GET['photos']) {
			$photos = $_GET['photos'];
		} else {
			$photos = 3;
		}
		if ($_GET['interval']) {
			$interval = $_GET['interval'];
		} else {
			$interval = 1;
		}
		// record photos
		$pipe = fopen("FIFO","w");
		for ($x=0; $x<$photos; $x++) {
			fwrite($pipe, "im");
			// the Pi needs time to process image, so sleep at least 1sec
			// no need to do sleep after the last loop though
			if ($x < $photos-1) {
				sleep($interval);
			}
		}
		fclose($pipe);
		break;
	default:
		echo "Unsupported command '" . $_GET['cmd'] . "'";
}

// remove lock
unlink($lockfile);
echo "Done!";
?>
