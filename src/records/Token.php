<?php

namespace nixon\instagram\records;

use craft\db\ActiveRecord;

/**
 * @property int $id ID
 * @property int $siteId Site ID
 * @property string $clientId Client ID
 * @property string $clientSecret Client Secret
 * @property string $token Token
 * @property \DateTime $expiryDate Token expiry date
 *
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Token extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%instagram}}';
    }
}
