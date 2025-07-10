<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

trait EncryptedRouteKey
{
    /**
     * Get the route key for the model (encrypted).
     *
     * @return string
     */
    public function getRouteKey()
    {
        return Crypt::encrypt($this->getKey());
    }

    /**
     * Retrieve the model for a bound value (decrypt the route key).
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $decryptedId = Crypt::decrypt($value);
            return $this->where($this->getKeyName(), $decryptedId)->first();
        } catch (DecryptException $e) {
            return null;
        }
    }

    /**
     * Get encrypted ID for use in URLs and responses
     *
     * @return string
     */
    public function getEncryptedIdAttribute()
    {
        return Crypt::encrypt($this->getKey());
    }

    /**
     * Decrypt an encrypted ID
     *
     * @param string $encryptedId
     * @return int|null
     */
    public static function decryptId($encryptedId)
    {
        try {
            return Crypt::decrypt($encryptedId);
        } catch (DecryptException $e) {
            return null;
        }
    }
}