<?php

namespace Fadion\BKT;

use Fadion\BKT\Exceptions\AuthenticateException;
use ArrayAccess;

class Authenticate implements ArrayAccess
{
    /**
     * @var string Kodi i monedhes Leke
     */
    const CURRENCY_ALL = '008';

    /**
     * @var string Kodi i monedhes Euro
     */
    const CURRENCY_EUR = '978';

    /**
     * @var string Kodi i monedhes Dollar
     */
    const CURRENCY_USD = '840';

    /**
     * @var array Te dhenat e porosise
     */
    protected $data = [];

    /**
     * Vendos te dhenat e porosise duke i bashkuar
     * me te dhenat baze
     * 
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $defaults = [
            'transactiontype' => 'Auth',
            'storetype' => '3D_pay_hosting',
            'instalment' => '',
            'rnd' => time(),
            'currency' => self::CURRENCY_ALL
        ];

        $this->data = array_merge($defaults, $data);
    }

    /**
     * Gjeneron hash-in
     * 
     * @return Authenticate
     */
    public function generate()
    {
        if (!$this->validate()) {
            throw new AuthenticateException("Important fields are missing. Please fill all the required fields before generating.");
        }

        $this->data['hash'] = $this->makeHash();

        return $this;
    }

    /**
     * Gjeneron input-et e formes
     * 
     * @return string
     */
    public function inputs()
    {
        $data = $this->data;
        $output = '';
        $inputs = [
            'clientid' => $data['clientid'],
            'amount' => $data['amount'],
            'islemtipi' => $data['transactiontype'],
            'taksit' => $data['instalment'],
            'oid' => $data['orderid'],
            'okUrl' => $data['okUrl'],
            'failUrl' => $data['failUrl'],
            'rnd' => $data['rnd'],
            'hash' => $data['hash'],
            'storetype' => $data['storetype'],
            'lang' => 'sq',
            'currency' => $data['currency'],
            'refreshtime' => '10',
            'Fismi' => ''
        ];

        foreach ($inputs as $name => $value) {
            $output .= '<input type="hidden" name="'.$name.'" value="'.$value.'">'."\n";
        }

        return trim($output, "\n");
    }

    /**
     * Kontrollon nese te dhenat e detyrueshme
     * jane vendosur
     * 
     * @return bool
     */
    protected function validate()
    {
        $data = $this->data;

        if (empty($data['clientid']) or empty($data['orderid']) or empty($data['okUrl'])
            or empty($data['failUrl']) or empty($data['currency']) or empty($data['storekey'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Gjeneron Hash per te autorizuar kerkesen
     */
    protected function makeHash()
    {
        $data = $this->data;

        $hash = $data['clientid'].$data['orderid'].$data['amount'].
                $data['okUrl'].$data['failUrl'].$data['transactiontype'].
                $data['instalment'].$data['rnd'].$data['storekey'];
        
        return base64_encode(pack('H*', sha1($hash)));
    }

    /**
     * Vendos clientid
     * 
     * @param string $value
     */
    public function setClientId($value)
    {
        $this->data['clientid'] = $value;
    }

    /**
     * Vendos storekey
     * 
     * @param string $value
     */
    public function setStoreKey($value)
    {
        $this->data['storekey'] = $value;
    }

    /**
     * Vendos amount
     * 
     * @param int $value
     */
    public function setAmount($value)
    {
        $this->data['amount'] = $value;
    }

    /**
     * Vendos orderid
     * 
     * @param string $value
     */
    public function setOrderId($value)
    {
        $this->data['orderid'] = $value;
    }

    /**
     * Vendos okUrl
     * 
     * @param string $value
     */
    public function setOkUrl($value)
    {
        $this->data['okUrl'] = $value;
    }

    /**
     * Vendos failUrl
     * 
     * @param string $value
     */
    public function setFailUrl($value)
    {
        $this->data['failUrl'] = $value;
    }

    /**
     * Vendos currency
     * 
     * @param string $value
     */
    public function setCurrency($value)
    {
        $this->data['currency'] = $value;
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

}