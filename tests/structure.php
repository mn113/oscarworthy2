<?php

class DB {
	protected $dbh;

	function create()
	function retrieve()
	function update()
	function delete()
}

class Entity {	// abstract class

	function tmdbLookup()
	function store() {
	function update() {
	function fetchLocal() {
	function delete()
}

class Film extends Entity {
	public $fid;
	public $cast;

	function tmdbLookup() {	// instantiated once on a film, multiple on a person	
	function store() {
//	function storeRoles() {	// smelly
	function update() {
	function fetchLocal() {
	function fetchCast() {
	function fetchCastFull() {	// includes recursive importing
}

class Person extends Entity {	// instantiated once on a person, multiple on a film
	public $pid;
	public $filmography;

	function tmdbLookup() {
	function store() {
//	function storeRoles() {	// smelly
	function update() {
	function fetchLocal() {
	function fetchFilmography() {
	function fetchFilmographyFull() {	// includes recursive importing
	function sortFilmography1() {
}

class Role {	// instantiated many times
	public $fid;
	public $pid;
	public $job;
	public $unique_hash;
	public $rating;

	function lookup() {
	function store() {
	function update() {
	function getRating() {	// possible duplicate of StarHelper->getMemberRatings()
	function setRating() {
	function setHash() {
}

class Vote {	// instantiated whenever a vote cast, to store it, then destroyed
	public $uid;
	public $unique_hash;
	public $rating;

	function store() {
	function update() {
}

class StarHelper {	// instantiated once at start of page load		// smelly

	function getUserRatings() {
	function getMemberRatings() {
	function displayStars() {}
}

class User {
	private $uid;
	private $role;
	private $username;
	private $password;
	private $email;

	function isLogged() {
	function isModerator() {
	function isAdmin() {

	function register() {
	function activate() {
	function login() {
	function logout() {
}

class Member extends User {
	function changePassword() {
	function resetPassword() {
	function suspendAccount() {
	function deleteAccount() {
}

class Password {
	private $uid;
	private $password;

	function createSalt() {
	function doubleHash() {
}

class Activation {
	public $email;

	function prepActivation() {
	function sendActivation() {
}
