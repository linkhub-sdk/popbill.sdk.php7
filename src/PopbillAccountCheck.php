<?php
/**
 * =====================================================================================
 * Class for base module for Popbill API SDK. It include base functionality for
 * RESTful web service request and parse json result. It uses Linkhub module
 * to accomplish authentication APIs.
 *
 * This module uses curl and openssl for HTTPS Request. So related modules must
 * be installed and enabled.
 *
 * https://www.linkhub.co.kr
 * Author : Jeong YoHan (code@linkhubcorp.com)
 * Written : 2020-07-01
 * Updated : 2025-01-14
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */

namespace Linkhub\Popbill;

class PopbillAccountCheck extends PopbillBase {

    public function __construct($LinkID,$SecretKey) {
        parent::__construct($LinkID,$SecretKey);
        $this->AddScope('182');
        $this->AddScope('183');
    }

    // 예금주성명 조회
    public function CheckAccountInfo($MemberCorpNum, $BankCode, $AccountNumber, $UserID = null) {
        if($this->isNullOrEmpty($MemberCorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($BankCode)) {
            throw new PopbillException('기관코드가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($AccountNumber)) {
            throw new PopbillException('계좌번호가 입력되지 않았습니다.');
        }

        $uri = "/EasyFin/AccountCheck";
        $uri .= "?c=" . $BankCode;
        $uri .= "&n=" . $AccountNumber;

        $result = $this->executeCURL($uri, $MemberCorpNum, $UserID, true, null, null);

        $AccountInfo = new AccountCheckInfo();
        $AccountInfo->fromJsonInfo($result);
        return $AccountInfo;
    }

    // 예금주실명 조회
    public function CheckDepositorInfo($MemberCorpNum, $BankCode, $AccountNumber, $IdentityNumType, $IdentityNum, $UserID = null) {
        if($this->isNullOrEmpty($MemberCorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($BankCode)) {
            throw new PopbillException('기관코드가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($AccountNumber)) {
            throw new PopbillException('계좌번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($IdentityNumType)) {
            throw new PopbillException('등록번호 유형이 입력되지 않았습니다.');
        }
        if(preg_match("/^[PB]$/", $IdentityNumType) == false){
            throw new PopbillException('등록번호 유형이 유효하지 않습니다.');
        }
        if($this->isNullOrEmpty($IdentityNum)) {
            throw new PopbillException('등록번호가 입력되지 않았습니다.');
        }
        if(preg_match("/^\\d+$/", $IdentityNum) == false){
            throw new PopbillException('등록번호는 숫자만 입력할 수 있습니다.');
        }

        $uri = "/EasyFin/DepositorCheck";
        $uri .= "?c=" . $BankCode;
        $uri .= "&n=" . $AccountNumber;
        $uri .= "&t=" . $IdentityNumType;
        $uri .= "&p=" . $IdentityNum;

        $result = $this->executeCURL($uri, $MemberCorpNum, $UserID, true, null, null);

        $DepositorCheckInfo = new DepositorCheckInfo();
        $DepositorCheckInfo->fromJsonInfo($result);
        return $DepositorCheckInfo;
    }

    // 발행 단가 확인
    public function GetUnitCost($CorpNum, $ServiceType, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ServiceType)) {
            throw new PopbillException('서비스 유형이 입력되지 않았습니다.');
        }

        $uri = "/EasyFin/AccountCheck/UnitCost?serviceType=". $ServiceType;

        return $this->executeCURL($uri, $CorpNum, $UserID)->unitCost;
    }

    // 과금정보 확인
    public function GetChargeInfo($CorpNum, $UserID = null, $ServiceType = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($ServiceType)) {
            throw new PopbillException('서비스 유형이 입력되지 않았습니다.');
        }
        
        $uri = '/EasyFin/AccountCheck/ChargeInfo?serviceType='. $ServiceType;

        $response = $this->executeCURL($uri, $CorpNum, $UserID);
        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }
}

class AccountCheckInfo
{
    public $resultCode;
    public $result;
    public $resultMessage;
    public $bankCode;
    public $accountNumber;
    public $accountName;
    public $checkDate;
    public $checkDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->resultCode) ? $this->resultCode = $jsonInfo->resultCode : null;
        isset($jsonInfo->result) ? $this->result = $jsonInfo->result : null;
        isset($jsonInfo->resultMessage) ? $this->resultMessage = $jsonInfo->resultMessage : null;
        isset($jsonInfo->bankCode) ? $this->bankCode = $jsonInfo->bankCode : null;
        isset($jsonInfo->accountNumber) ? $this->accountNumber = $jsonInfo->accountNumber : null;
        isset($jsonInfo->accountName) ? $this->accountName = $jsonInfo->accountName : null;
        isset($jsonInfo->checkDate) ? $this->checkDate = $jsonInfo->checkDate : null;
        isset($jsonInfo->checkDT) ? $this->checkDT = $jsonInfo->checkDT : null;
    }
}

class DepositorCheckInfo
{
    public $result;
    public $resultMessage;
    public $bankCode;
    public $accountNumber;
    public $accountName;
    public $identityNumType;
    public $identityNum;
    public $checkDate;
    public $checkDT;

    function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->result) ? $this->result = $jsonInfo->result : null;
        isset($jsonInfo->resultMessage) ? $this->resultMessage = $jsonInfo->resultMessage : null;
        isset($jsonInfo->bankCode) ? $this->bankCode = $jsonInfo->bankCode : null;
        isset($jsonInfo->accountNumber) ? $this->accountNumber = $jsonInfo->accountNumber : null;
        isset($jsonInfo->accountName) ? $this->accountName = $jsonInfo->accountName : null;
        isset($jsonInfo->identityNumType) ? $this->identityNumType = $jsonInfo->identityNumType : null;
        isset($jsonInfo->identityNum) ? $this->identityNum = $jsonInfo->identityNum : null;
        isset($jsonInfo->checkDate) ? $this->checkDate = $jsonInfo->checkDate : null;
        isset($jsonInfo->checkDT) ? $this->checkDT = $jsonInfo->checkDT : null;
    }
}

?>
