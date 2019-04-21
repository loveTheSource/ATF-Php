<?php

namespace ATFApp\Helper;

use ATFAPP\ProjectConstants;

use ATFApp\Core;
use ATFApp\BasicFunctions;

class CsrfTokens {

    private $tokenLength = 23;
    private $tokenPrefix = 'atfcsrf_';
	
	public function __construct() {
    }
	
    public function getNewToken() {
        $token = $this->generateToken();
        $this->saveToken($token);
        return $token;
    }

    public function validateToken($token) {        
        $all = $this->getTokens();

        if (is_string($token) 
        && strlen($token) === ($this->tokenLength + strlen($this->tokenPrefix)) 
        && array_key_exists($token, $all)) {
            $this->setUsedToken($token);
            $this->deleteToken($token);
            $this->cleanupTokens();

            return true;
        }
        $this->cleanupTokens();
        return false;
    }

    /**
     * add currently used csrf token to tokens again
     * (required for redirects)
     */
    public function restoreUsedToken() {
        $usedToken = $this->getUsedToken();
        if ($usedToken) {
            $this->saveToken($usedToken, true);
        }
    }


    private function setUsedToken($token) {
        Core\Request::setParamGlobals(ProjectConstants::KEY_GLOBAL_USED_CSRF_TOKEN, $token);
    }
    private function getUsedToken() {
        return Core\Request::getParamGlobals(ProjectConstants::KEY_GLOBAL_USED_CSRF_TOKEN);
    }

	/**
	 * get tokens from session
	 *
	 * @return array
	 */
    private function getTokens() {
        $tokens = Core\Request::getParamSession(ProjectConstants::KEY_SESSION_CSRF_TOKENS);
        if (is_array($tokens)) {
            return $tokens;
        }
        return [];
    }

    private function setTokens(array $tokens) {
        Core\Request::setParamSession(ProjectConstants::KEY_SESSION_CSRF_TOKENS, $tokens);
    }

    private function saveToken($token, $restoreUsedToken=false) {
        $all = $this->getTokens();
        if (is_null($all)) {
            $all = [];
        }

        $newToken = [
            'timstamp' => time(),
            'valid_until' => time() + ProjectConstants::CSRF_TOKENS_EXPIRY
        ];
        if ($restoreUsedToken) {
            $newToken['restored'] = 1;
        }

        $all[$token] = $newToken;
        $this->setTokens($all);
    }

    private function cleanupTokens() {
        $all = $this->getTokens();
        $now = time();
        $changed = false;
        foreach($all as $token => $conf) {
            if ($conf['valid_until'] < $now) {
                unset($all[$token]);
                $changed = true;
            }
        }

        if ($changed) {
            $this->setTokens($all);
        }
    }

    private function deleteToken($token) {
        $all = $this->getTokens();
        if (array_key_exists($token, $all)) {
            unset($all[$token]);
            $this->setTokens($all);
        }
    }

    private function generateToken() {
        return uniqid($this->tokenPrefix, true);
    }
}