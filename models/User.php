<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use app\models\UserProfile;
use app\models\UserProvider;
use app\models\query\UserQuery;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $password_reset_token
 * @property string $email_confirm_token
 * @property string $auth_key
 * @property string $date_confirm
 * @property string $date_create
 * @property string $date_update
 * @property string $date_login
 * @property integer $ip
 * @property string $role
 * @property integer $status
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 1;
    const STATUS_BLOCKED = 2;

    const ROLE_SUPERUSER = 'SuperUser';

    /**
     * @var string
     */
    public $passwordNew;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'date_create' => Yii::t('app', 'Date create'),
            'date_update' => Yii::t('app', 'Date update'),
            'date_login' => Yii::t('app', 'Last login'),
            'ip' => Yii::t('app', 'IP'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Status'),

            'passwordNew' => Yii::t('app', 'New password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function transactions()
    {
        return [
            'create' => self::OP_ALL,
            'update' => self::OP_ALL,
            'delete' => self::OP_ALL,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    /**
     * @param array $attributes
     */
    public function setProfile($attributes = [])
    {
        return $this->populateRelation('profile', new UserProfile($attributes));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProviders()
    {
        return $this->hasMany(UserProvider::class, ['user_id' => 'id']);
    }

    /**
     * @param array $attributes
     */
    public function setProviders($attributes = [])
    {
        return $this->populateRelation('providers', [new UserProvider($attributes)]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'role']);
    }

    /**
     * @inheritdoc
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
                if (!Yii::$app instanceof \yii\console\Application) {
                    $this->ip = ip2long(Yii::$app->request->getUserIP());
                }

                if ($this->profile === null) {
                    $this->setProfile();
                }
            }

            if (!empty($this->passwordNew)) {
                $this->setPassword($this->passwordNew);
            }

            return true;
        } // @codeCoverageIgnore

        return false; // @codeCoverageIgnore
    }

    /**
     * @inheritdoc
     * @property \app\models\UserProfile $profile
     * @property \app\models\UserProvider $providers
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->profile !== null) {
            $this->link('profile', $this->profile);
        }

        if ($this->providers !== null && count($this->providers)) {
            foreach ($this->providers as $provider) {
                if ($provider) {
                    $this->link('providers', $provider);
                }
            }
        }
    }

    /**
     * @inheritdoc
     * @param boolean $runValidation
     * @param array $attributeNames
     * @return boolean
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        return $this->getDb()->transaction(function () use ($runValidation, $attributeNames) {
            return parent::save($runValidation, $attributeNames);
        });
    }

    /**
     * Get all statuses
     *
     * @param string[]
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DELETED => Yii::t('app', 'Deleted'),
            self::STATUS_BLOCKED => Yii::t('app', 'Locked'),
            self::STATUS_ACTIVE  => Yii::t('app', 'Active'),
        ];
    }

    /**
     * Get statuse name
     *
     * @return string
     */
    public function getStatusName(): string
    {
        $statuses = self::getStatuses();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : '';
    }

    /**
     * Is it deleted?
     *
     * @param bool
     */
    public function isDeleted(): bool
    {
        return $this->status == self::STATUS_DELETED;
    }

    /**
     * Is it blocked?
     *
     * @param bool
     */
    public function isBlocked(): bool
    {
        return $this->status == self::STATUS_BLOCKED;
    }

    /**
     * Is it active?
     *
     * @param bool
     */
    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Is it confirmed?
     *
     * @param bool
     */
    public function isConfirmed(): bool
    {
        return strtotime($this->date_confirm) > 0;
    }

    /**
     * Is SuperUser?
     *
     * @return bool
     */
    public function isSuperUser(): bool
    {
        return $this->role === self::ROLE_SUPERUSER;
    }

    /**
     * Set confirmed
     */
    public function setConfirmed(): void
    {
        $this->email_confirm_token = null;
        $this->date_confirm = new \yii\db\Expression('NOW()');
    }

    /**
     * Get status description
     *
     * @return string
     */
    public function getStatusDescription(): string
    {
        if ($this->status == self::STATUS_BLOCKED) {
            return Yii::t('app', 'Your account has been suspended');
        } elseif ($this->status == self::STATUS_DELETED) {
            return Yii::t('app', 'Your account has been deleted');
        }
        return Yii::t('app', 'Your account is activated');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        if (empty($this->password)) {
            return false;
        }

        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Set a new password
     *
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Set new password reset token
     *
     * @param string $token
     */
    public function setPasswordResetToken(string $token): void
    {
        $this->password_reset_token = $token;
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    /**
     * Set new confirm email token
     *
     * @param string $token
     */
    public function setEmailConfirmToken(string $token): void
    {
        $this->email_confirm_token = $token;
        $this->date_confirm = null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne([$id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('findIdentityByAccessToken is not implemented.');
    }

    /**
     * Update date login
     */
    public function updateDateLogin(): void
    {
        $this->updateAttributes([
            'date_login' => new \yii\db\Expression('NOW()'),
            'ip' => ip2long(Yii::$app->request->getUserIP())
        ]);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        Yii::$app->authManager->revokeAll($this->id);

        if ($this->profile !== null) {
            $this->profile->delete();
        }

        return true;
    }
}
