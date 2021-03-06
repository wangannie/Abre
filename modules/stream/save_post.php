<?php

	/*
	* Copyright (C) 2016-2018 Abre.io Inc.
	*
	* This program is free software: you can redistribute it and/or modify
    * it under the terms of the Affero General Public License version 3
    * as published by the Free Software Foundation.
	*
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU Affero General Public License for more details.
	*
    * You should have received a copy of the Affero General Public License
    * version 3 along with this program.  If not, see https://www.gnu.org/licenses/agpl-3.0.en.html.
    */

	//Required configuration files
	require_once(dirname(__FILE__) . '/../../core/abre_verification.php');
	require(dirname(__FILE__) . '/../../core/abre_dbconnect.php');
	require_once(dirname(__FILE__) . '/../../core/abre_functions.php');


  $postAuthor = $_SESSION['useremail'];
  $sql = "SELECT firstname, lastname FROM directory WHERE email = '$postAuthor'";
  $result = $db->query($sql);
  $resultrow = $result->fetch_assoc();
  $authorFirstName = $resultrow['firstname'];
  $authorLastName = $resultrow['lastname'];

  if($_POST['post_title'] != ""){
    $postTitle = $_POST['post_title'];
  }else{
		$response = array("status" => "Error", "message" => "Error! You did not provide a post title!");
		header("Content-Type: application/json");
		echo json_encode($response);
		exit;
  }
  if($_POST['post_stream'] != ""){
    $postStream = $_POST['post_stream'];
  }else{
		$response = array("status" => "Error", "message" => "Please provide a post stream!");
		header("Content-Type: application/json");
		echo json_encode($response);
		exit;
  }

	$sql = "SELECT `group`, staff_building_restrictions, student_building_restrictions, color FROM streams WHERE title = '$postStream' LIMIT 1";
	$result = $db->query($sql);
	$value = $result->fetch_assoc();
	$postGroup = $value['group'];
	$staffRestrictions = $value['staff_building_restrictions'];
	$studentRestrictions = $value['student_building_restrictions'];
	$color = $value['color'];

	if($staffRestrictions == ""){
		$staffRestrictions = "No Restrictions";
	}

	if($studentRestrictions == ""){
		$studentRestrictions = "No Restrictions";
	}

  if($_POST['post_content'] != ""){
    $postContent = $_POST['post_content'];
  }else{
		$response = array("status" => "Error", "message" => "Please provide content for your post!");
		header("Content-Type: application/json");
		echo json_encode($response);
		exit;
  }


  //Save POST Image
  $image_file_name = "";
	if($_FILES['customimage']['name']){

		//Get file information
		$file = $_FILES['customimage']['name'];
		$fileextention = pathinfo($file, PATHINFO_EXTENSION);
		$cleanfilename = basename($file);
		$image_file_name = time() . "_post." . $fileextention;
		$uploaddir = $portal_path_root . "/../$portal_private_root/stream/cache/images/" . $image_file_name;

		//Upload new image
		$postimage = $uploaddir;
		move_uploaded_file($_FILES['customimage']['tmp_name'], $postimage);

		//Resize image
		ResizeImage($uploaddir, "1000", "90");

	}


  $stmt = $db->stmt_init();
  $sql = "INSERT INTO stream_posts (post_author, author_firstname, author_lastname, post_groups, post_title, post_stream, post_content, post_image, color, staff_building_restrictions, student_building_restrictions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt->prepare($sql);
  $stmt->bind_param("sssssssssss", $postAuthor, $authorFirstName, $authorLastName, $postGroup, $postTitle, $postStream, $postContent, $image_file_name, $color, $staffRestrictions, $studentRestrictions);
  $stmt->execute();
	if($stmt->error != ""){
		$stmt->close();
		$db->close();
		$response = array("status" => "Error", "message" => "There was a problem saving your post. Please try again.");
		header("Content-Type: application/json");
		echo json_encode($response);
		exit;
	}
  $stmt->close();

  $db->close();
  $response = array("status" => "Success", "message" => "Your post was saved successfully!");
  header("Content-Type: application/json");
  echo json_encode($response);


  ?>