<?php
class Review {

	// constructor won't be exported
	function Review() {
		if (!isset($_SESSION['reviews'])) {
			$_SESSION['reviews'] = array();	
		}
	}

	// data is an array of objects
	function newReview($data) {
		$_SESSION['reviews'][] = $data;

		$key = count($_SESSION['reviews'])-1;

		return "<div onclick='editReview($key,this)'><div class='name'>$data->name</div><div class='review'>$data->review</div></div>";
	}

	function updateReview($key,$data) {
		$_SESSION['reviews'][$key] = $data;
		return array($key,"<div onclick='editReview($key,this)'><div class='name'>$data->name</div><div class='review'>$data->review</div></div>");
	}
}
?>
