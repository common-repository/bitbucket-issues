<?php
/*
Plugin Name: BitBucket Issues
Description: Aggregate issues from multiple bitbucket repositories
Plugin URI: https://bitbucket.org/perchten/wordpress-bitbucket-issues-plugin
Version: 0.1
License: GPL
Author: Perch Ten Design
Author URI: perchten.co.uk
*/

class BitbucketBroker {
    
    var $urlBase = "https://api.bitbucket.org/1.0/";
    var $username;
    var $password;
    
    function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    function send($url) {
        // Initialize session and set URL.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Nasty hack to allow all SSL certs to verify. Should replace with proper reference to local CA store.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Authenticate
        curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
        
        
        // Get the response and close the channel.
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
    
    function getRepositories() {
        $url = $this->urlBase."user/repositories";
        return $this->send($url);
    }

    function getRepository($repo){
        $url = $this->urlBase."repositories/$this->username/$repo";
        return $this->send($url);
    }
    function getIssues($repository) {
        $url = $this->urlBase."repositories/$this->username/$repository/issues?status=!resolved";
        $issues = $this->send($url);
        return $issues->issues;
    }
}

class BitbucketIssues {
    
    static $broker = false;
    static $formPrefix = "bitbucketissues_";
    static $dbPrefix = "bbi_";   
    
    static function formField($name) {
        return self::$formPrefix.$name;
    }
    static function dbField($name) {
        return self::$dbPrefix.$name;
    }    
    static function dbValue($field) {
        return get_option(self::dbField($field));
    }
    static function broker() {
        if ( !self::$broker ) {
            $username = get_option(self::dbField("username"));
            $password = get_option(self::dbField("password"));            
            self::$broker = new BitbucketBroker($username, $password);
        }
        return self::$broker;
    }
    
    static $selectedRepos = false;
    static function loadSelectedRepos($ns){
        if ( !self::$selectedRepos || !is_array(self::$selectedRepos) || !array_key_exists($ns,self::$selectedRepos )) {
            if ( !is_array(self::$selectedRepos ) )
                self::$selectedRepos = array();
            self::$selectedRepos[$ns] = self::dbValue($ns."repos");
        }        
    }
    static function isRepoSelected($repo, $ns="public_") {        
        self::loadSelectedRepos($ns);
        return in_array($repo, self::$selectedRepos[$ns]);
    }
    
    
    private static function updateOptions() {
        if ( array_key_exists(BitbucketIssues::formField("auth"), $_POST) ) {
            foreach ( array("username","password") as $field ) {
                update_option(self::dbField($field), $_POST[BitbucketIssues::formField($field)]);
            }
            self::$broker = false;
            ?><div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div><?php
        }
    }
    private static function updateRepos() {
        if ( array_key_exists(BitbucketIssues::formField($ns."repos"), $_POST) ) {
            foreach ( array("public_","loggedin_") as $ns ) {
                $repos = $_POST[self::formField($ns."repos")];
                update_option(self::dbField($ns."repos"),$repos);                
            }
            ?><div class="updated"><p><strong><?php _e('Repositories updated.' ); ?></strong></p></div><?php
        }            
    }
    
    static function menu() {
        self::updateOptions();
        self::updateRepos();
        include("bitbucket-issues_form.php");        
    } 
    
    static function listRepositories($ns) {
        self::loadSelectedRepos($ns);
        return self::$selectedRepos[$ns];
    }
    
    static function shortcode() {
        ob_start();
        include("bitbucket-issues_shortcode.php");
        $content = ob_get_clean();
        return $content;
    }
    
}



/*
 * 
 * ADMIN MENU
 * 
 * 
 */
function bitbucketIssues_menu() {
    add_options_page("BitBucket Issues options", "BitBucket Issues", "manage_options","bitbucket-issues",array("BitbucketIssues","menu"));
}
add_action('admin_menu',"bitbucketIssues_menu");


/*
 * 
 * SHORTCODE
 * 
 * 
 */
add_shortcode("bitbucket-issues", array("BitbucketIssues","shortcode"));




?>