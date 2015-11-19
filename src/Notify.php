<?php

namespace Fadion\BKT;

use Exception;

class Notify {

    /**
     * @var Te dhenat POST
     */
    protected $post;

    /**
     * @var Vlera e storekey
     */
    protected $storekey;

    /**
     * Vendos te dhenat post dhe storekey
     * 
     * @param array $post
     * @param string $storekey
     */
    public function __construct(array $post, $storekey)
    {
        $this->post = $post;
        $this->storekey = $storekey;
    }

    /**
     * Factory per ta krijuar objektin
     * 
     * @param array $post
     * @param string $storekey
     */
    public static function make(array $post, $storekey)
    {
        return new static($post, $storekey);
    }

    /**
     * Njofton nese porosia eshte kryer me sukses
     * 
     * @return bool
     */
    public function success()
    {
        if (! $this->validate()) {
            throw new Exception("POST data are missing critical information.");
        }

        $response = $this->post['response'];
        $hashParams = $this->post['HASHPARAMS'];
        $hash = $this->post['HASH'];

        if ($response == 'Approved' and $this->checkHash($hashParams, $hash, $storekey)) {
            return true;
        }

        return false;
    }

    /**
     * Njofton nese porosia nuk eshte kryer
     * 
     * @return bool
     */
    public function error()
    {
        return ! $this->success();
    }

    /**
     * Validon nese te dhenat nga POST jane te plota
     * dhe hash eshte i sakte
     * 
     * @return bool
     */
    protected function validate()
    {
        $post = $this->post;

        if (empty($post['response']) or empty($post['HASHPARAMS']) or empty($post['HASH'])) {
            return false;
        }

        return true;
    }

    /**
     * Kontrollon nese hash i ardhur nga serveri
     * perputhet me ate te gjeneruar lokalisht
     * 
     * @param string $hashParams
     * @param string $hash
     * @param string $storekey
     * @return bool
     */
    protected function checkHash($hashParams, $hash, $storekey) {
        $paramsval = '';
        $index1 = 0;

        while ($index1 < strlen($hashParams)) {
            $index2 = strpos($hashParams, ':', $index1);
            $vl = $this->post[substr($hashParams, $index1, $index2 - $index1)];

            if ($vl == null) {
                $vl = '';
            }

            $paramsval = $paramsval.$vl;
            $index1 = $index2 + 1;
        }

        $hashval = $paramsval.$storekey;
        $hashServer = base64_encode(pack('H*', sha1($hashval)));

        return $hash == $hashServer;
    }

}
