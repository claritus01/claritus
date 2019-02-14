<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cc_users".
 *
 * @property integer $id
 * @property integer $role
 * @property integer $user_type
 * @property string $password
 * @property integer $status
 * @property integer $is_delete
 * @property string $created_on
 * @property integer $created_by
 * @property string $modified_on
 * @property integer $modified_by
 *
 * @property AdminPersonal[] $adminPersonals
 * @property AdminPersonal[] $adminPersonals0
 * @property AdminPersonal[] $adminPersonals1
 * @property Cities[] $cities
 * @property Configurations[] $configurations
 * @property Configurations[] $configurations0
 * @property Countries[] $countries
 * @property Countries[] $countries0
 * @property Devices[] $devices
 * @property Devices[] $devices0
 * @property Devices[] $devices1
 * @property LookupTypes[] $lookupTypes
 * @property LookupTypes[] $lookupTypes0
 * @property Lookups[] $lookups
 * @property Lookups[] $lookups0
 * @property PasswordHistory[] $passwordHistories
 * @property PasswordHistory[] $passwordHistories0
 * @property PasswordHistory[] $passwordHistories1
 * @property Permissions[] $permissions
 * @property Permissions[] $permissions0
 * @property RolePermissions[] $rolePermissions
 * @property RolePermissions[] $rolePermissions0
 * @property States[] $states
 * @property SystemTokens[] $systemTokens
 * @property SystemTokens[] $systemTokens0
 * @property SystemTokens[] $systemTokens1
 * @property UserPersonalDetails[] $userPersonalDetails
 * @property UserPersonalDetails[] $userPersonalDetails0
 * @property UserPersonalDetails[] $userPersonalDetails1
 * @property Lookups $role0
 * @property Lookups $status0
 * @property Users $createdBy
 * @property Users[] $users
 * @property Users $modifiedBy
 * @property Users[] $users0
 * @property Lookups $userType
 */
class Users extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cc_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role', 'user_type', 'is_delete', 'created_on', 'modified_on'], 'required'],
            [['role', 'user_type', 'status', 'is_delete', 'created_by', 'modified_by'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            [['password'], 'string', 'max' => 80],
            [['password'], 'string', 'min' =>8],            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role' => 'Role',
            'user_type' => 'User Type',
            'password' => 'Password',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_on' => 'Created On',
            'created_by' => 'Created By',
            'modified_on' => 'Modified On',
            'modified_by' => 'Modified By',
        ];
    }

    ////////////////////////////////////////////////////////////////////////////////////
    //
    /**
    * @inheritdoc
    */
    public static function findIdentity($id){
        return static::findOne(['Id' => $id]);
    }
    
    /**
    * @inheritdoc
    */
    public static function findIdentityByAccessToken($token, $type = null){
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }
    
    /**
    * Finds user by username
    *
    * @param string $username
    * @return static|null
    */
    public static function findByUsername($username){
        return static::findOne(['Email' => $username]);
    }
    
    /**
    * Finds user by password reset token
    *
    * @param string $token password reset token
    * @return static|null
    */
    public static function findByPasswordResetToken($token){
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }
    
    /**
    * Finds out if password reset token is valid
    *
    * @param string $token password reset token
    * @return boolean
    */
    public static function isPasswordResetTokenValid($token){
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }
    
    
    /**
    * @inheritdoc
    */
    public function getId(){
        return $this->getPrimaryKey();
    }
    
    /**
    * @inheritdoc
    */
    public function getAuthKey(){
        return true;
        //return $this->auth_key;
    }
    
    /**
    * @inheritdoc
    */
    public function validateAuthKey($authKey){
        return $this->getAuthKey() === $authKey;
    }
    
    ///////////////////////////////////////////////////////////////////////////////////
    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminPersonals()
    {
        return $this->hasOne(AdminPersonal::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminPersonals0()
    {
        return $this->hasMany(AdminPersonal::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminPersonals1()
    {
        return $this->hasMany(AdminPersonal::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(Cities::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigurations()
    {
        return $this->hasMany(Configurations::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigurations0()
    {
        return $this->hasMany(Configurations::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountries()
    {
        return $this->hasMany(Countries::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountries0()
    {
        return $this->hasMany(Countries::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevices()
    {
        return $this->hasMany(Devices::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevices0()
    {
        return $this->hasMany(Devices::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevices1()
    {
        return $this->hasMany(Devices::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLookupTypes()
    {
        return $this->hasMany(LookupTypes::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLookupTypes0()
    {
        return $this->hasMany(LookupTypes::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLookups()
    {
        return $this->hasMany(Lookups::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLookups0()
    {
        return $this->hasMany(Lookups::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPasswordHistories()
    {
        return $this->hasMany(PasswordHistory::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPasswordHistories0()
    {
        return $this->hasMany(PasswordHistory::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPasswordHistories1()
    {
        return $this->hasMany(PasswordHistory::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permissions::className(), ['creatad_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions0()
    {
        return $this->hasMany(Permissions::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolePermissions()
    {
        return $this->hasMany(RolePermissions::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolePermissions0()
    {
        return $this->hasMany(RolePermissions::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStates()
    {
        return $this->hasMany(States::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystemTokens()
    {
        return $this->hasMany(SystemTokens::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystemTokens0()
    {
        return $this->hasMany(SystemTokens::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystemTokens1()
    {
        return $this->hasMany(SystemTokens::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPersonalDetails()
    {
        return $this->hasMany(UserPersonalDetails::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPersonalDetails0()
    {
        return $this->hasMany(UserPersonalDetails::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPersonalDetails1()
    {
        return $this->hasMany(UserPersonalDetails::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole0()
    {
        return $this->hasOne(Lookups::className(), ['id' => 'role']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus0()
    {
        return $this->hasOne(Lookups::className(), ['id' => 'status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Users::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy()
    {
        return $this->hasOne(Users::className(), ['id' => 'modified_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers0()
    {
        return $this->hasMany(Users::className(), ['modified_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserType()
    {
        return $this->hasOne(Lookups::className(), ['id' => 'user_type']);
    }
}
