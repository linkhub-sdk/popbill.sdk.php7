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
 * Written : 2019-02-08
 * Updated : 2025-01-14
 *
 * Thanks for your interest.
 * We welcome any suggestions, feedbacks, blames or anything.
 * ======================================================================================
 */

namespace Linkhub\Popbill;

class PopbillHTTaxinvoice extends PopbillBase {

    public function __construct ( $LinkID, $SecretKey )
    {
        parent::__construct ( $LinkID, $SecretKey );
        $this->AddScope ( '111' );
    }

    // 과금정보 확인
    public function GetChargeInfo($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        
        $response = $this->executeCURL('/HomeTax/Taxinvoice/ChargeInfo', $CorpNum, $UserID);

        $ChargeInfo = new ChargeInfo();
        $ChargeInfo->fromJsonInfo($response);

        return $ChargeInfo;
    }

    // 수집 요청
    public function RequestJob($CorpNum, $TIType, $DType, $SDate, $EDate, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($TIType)) {
            throw new PopbillException('세금계산서 유형이 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($SDate)) {
            throw new PopbillException('시작일자가 입력되지 않았습니다.');
        }
        if(!$this->isValidDate($SDate)) {
            throw new PopbillException('시작일자가 유효하지 않습니다.');
        }
        if($this->isNullOrEmpty($EDate)) {
            throw new PopbillException('종료일자가 입력되지 않았습니다.');
        }
        if(!$this->isValidDate($EDate)) {
            throw new PopbillException('종료일자가 유효하지 않습니다.');
        }

        $uri = '/HomeTax/Taxinvoice/'.$TIType;
        $uri .= '?DType='.$DType.'&SDate='.$SDate.'&EDate='.$EDate;

        return $this->executeCURL($uri, $CorpNum, $UserID, true, "", "")->jobID;
    }

    // 수집 상태 확인
    public function GetJobState($CorpNum, $JobID, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($JobID)) {
            throw new PopbillException('작업아이디(JobID)가 입력되지 않았습니다.');
        }
        if(strlen ( $JobID ) != 18) {
            throw new PopbillException ('작업아이디(JobID)가 유효하지 않습니다.');
        }

        $response = $this->executeCURL('/HomeTax/Taxinvoice/'.$JobID.'/State', $CorpNum, $UserID);

        $JobState = new HTTIJobState();
        $JobState->fromJsonInfo($response);

        return $JobState;
    }

    // 수집 상태 목록 확인
    public function ListActiveJob($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $result = $this->executeCURL('/HomeTax/Taxinvoice/JobList', $CorpNum, $UserID);

        $JobList = array();

        for ( $i = 0; $i < Count ( $result ) ;  $i++ ) {
            $JobState = new HTTIJobState();
            $JobState->fromJsonInfo($result[$i]);
            $JobList[$i] = $JobState;
        }

        return $JobList;
    }

    // 수집 결과 조회
    public function Search($CorpNum, $JobID, $Type = array(), $TaxType = array(), $PurposeType = array(), $TaxRegIDYN = null, $TaxRegIDType = null, $TaxRegID = null, $Page = null, $PerPage = null, $Order = null, $UserID = null, $SearchString = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($JobID)) {
            throw new PopbillException('작업아이디(JobID)가 입력되지 않았습니다.');
        }
        if(strlen ( $JobID ) != 18) {
            throw new PopbillException ('작업아이디(JobID)가 유효하지 않습니다.');
        }

        $uri = '/HomeTax/Taxinvoice/' . $JobID . '?Type=';
        
        
        if(!$this->isNullOrEmpty($Type)) {
            $uri .= implode ( ',', $Type );
        }
        if(!$this->isNullOrEmpty($TaxType)) {
            $uri .= '&TaxType=' . implode ( ',', $TaxType );
        }
        if(!$this->isNullOrEmpty($PurposeType)) {
            $uri .= '&PurposeType=' . implode ( ',', $PurposeType );
        }
        if(!$this->isNullOrEmpty($TaxRegIDYN)) {
            $uri .= '&TaxRegIDYN=' . $TaxRegIDYN;
        }
        if(!$this->isNullOrEmpty($TaxRegIDType)) {
            $uri .= '&TaxRegIDType=' . $TaxRegIDType;
        }
        if(!$this->isNullOrEmpty($TaxRegID)) {
            $uri .= '&TaxRegID=' . $TaxRegID;
        }
        if(!$this->isNullOrEmpty($Page)) {
            $uri .= '&Page=' . $Page;
        }
        if(!$this->isNullOrEmpty($PerPage)) {
            $uri .= '&PerPage=' . $PerPage;
        }
        if(!$this->isNullOrEmpty($Order)) {
            $uri .= '&Order=' . $Order;
        }
        if(!$this->isNullOrEmpty($SearchString)) {
            $uri .= '&SearchString=' . urlencode($SearchString);
        }

        $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

        $SearchResult = new HTTaxinvoiceSearch();
        $SearchResult->fromJsonInfo($response);

        return $SearchResult;
    }

    // 수집결과 요약정보 조회
    public function Summary($CorpNum, $JobID, $Type = array(), $TaxType = array(), $PurposeType = array(), $TaxRegIDYN = null, $TaxRegIDType = null, $TaxRegID = null, $UserID = null, $SearchString = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($JobID)) {
            throw new PopbillException('작업아이디(JobID)가 입력되지 않았습니다.');
        }
        if(strlen ( $JobID ) != 18) {
            throw new PopbillException ('작업아이디(JobID)가 유효하지 않습니다.');
        }

        $uri = '/HomeTax/Taxinvoice/' . $JobID .  '/Summary' . '?Type=';

        if(!$this->isNullOrEmpty($Type)) {
            $uri .= implode ( ',', $Type );
        }
        if(!$this->isNullOrEmpty($TaxType)) {
            $uri .= '&TaxType=' . implode ( ',', $TaxType );
        }
        if(!$this->isNullOrEmpty($PurposeType)) {
            $uri .= '&PurposeType=' . implode ( ',', $PurposeType );
        }
        if(!$this->isNullOrEmpty($TaxRegIDYN)) {
            $uri .= '&TaxRegIDYN=' . $TaxRegIDYN;
        }
        if(!$this->isNullOrEmpty($TaxRegIDType)) {
            $uri .= '&TaxRegIDType=' . $TaxRegIDType;
        }
        if(!$this->isNullOrEmpty($TaxRegID)) {
            $uri .= '&TaxRegID=' . $TaxRegID;
        }
        if(!$this->isNullOrEmpty($SearchString)) {
            $uri .= '&SearchString=' . urlencode($SearchString);
        }

        $response = $this->executeCURL ( $uri, $CorpNum, $UserID );

        $Summary = new HTTaxinvoieSummary();
        $Summary->fromJsonInfo ( $response ) ;

        return $Summary;
    }

    // 상세정보 확인(JSON)
    public function GetTaxinvoice($CorpNum, $NTSConfirmNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($NTSConfirmNum)) {
            throw new PopbillException('국세청승인번호가 입력되지 않았습니다.');
        }
        if(strlen ($NTSConfirmNum) != 24) {
            throw new PopbillException ('국세청승인번호가 유효하지 않습니다.');
        }

        $response = $this->executeCURL( '/HomeTax/Taxinvoice/' . $NTSConfirmNum, $CorpNum, $UserID );

        $HTTaxinvoice = new HTTaxinvoice();
        $HTTaxinvoice->fromJsonInfo ( $response ) ;

        return $HTTaxinvoice;
    }

    // 상세정보 확인(XML)
    public function GetXML($CorpNum, $NTSConfirmNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($NTSConfirmNum)) {
            throw new PopbillException('국세청승인번호가 입력되지 않았습니다.');
        }
        if(strlen ( $NTSConfirmNum ) != 24) {
            throw new PopbillException ('국세청승인번호가 유효하지 않습니다.');
        }

        $response = $this->executeCURL ( '/HomeTax/Taxinvoice/' . $NTSConfirmNum .'?T=xml', $CorpNum, $UserID );

        $HTTaxinvoiceXML = new HTTaxinvoiceXML();
        $HTTaxinvoiceXML->fromJsonInfo ( $response ) ;

        return $HTTaxinvoiceXML;
    }

    // 정액제 서비스 신청 팝업 URL
    public function GetFlatRatePopUpURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL ( '/HomeTax/Taxinvoice?TG=CHRG', $CorpNum, $UserID )->url;
    }

    // 정액제 서비스 상태 확인
    public function GetFlatRateState($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        $response = $this->executeCURL ( '/HomeTax/Taxinvoice/Contract', $CorpNum, $UserID ) ;

        $FlatRateState = new HTTIFlatRate();
        $FlatRateState->fromJsonInfo ( $response );

        return $FlatRateState;
    }

    // 홈택스수집 인증 관리 팝업 URL
    public function GetCertificatePopUpURL($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL ( '/HomeTax/Taxinvoice?TG=CERT', $CorpNum, $UserID )->url;
    }

    // 인증서 만료일자 확인
    public function GetCertificateExpireDate($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL ('/HomeTax/Taxinvoice/CertInfo', $CorpNum )->certificateExpiration;
    }

    // 홈택스 전자세금계산서 보기 팝업 URL
    public function GetPopUpURL($CorpNum ,$NTSConfirmNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($NTSConfirmNum)) {
            throw new PopbillException('국세청승인번호가 입력되지 않았습니다.');
        }
        if(strlen ( $NTSConfirmNum ) != 24) {
            throw new PopbillException ('국세청승인번호가 유효하지 않습니다.');
        }

        $response = $this->executeCURL('/HomeTax/Taxinvoice/'.$NTSConfirmNum.'/PopUp', $CorpNum, $UserID);
        
        return $response->url;
    }

    // 홈택스 전자세금계산서 인쇄 팝업 URL
    public function GetPrintURL($CorpNum ,$NTSConfirmNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($NTSConfirmNum)) {
            throw new PopbillException('국세청승인번호가 입력되지 않았습니다.');
        }
        if(strlen ( $NTSConfirmNum ) != 24) {
            throw new PopbillException ('국세청승인번호가 유효하지 않습니다.');
        }

        $response = $this->executeCURL('/HomeTax/Taxinvoice/'.$NTSConfirmNum.'/Print', $CorpNum, $UserID);
        
        return $response->url;
    }

    // 인증서 로그인 테스트
    public function CheckCertValidation($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }

        return $this->executeCURL('/HomeTax/Taxinvoice/CertCheck', $CorpNum);
    }

    // 부서사용자 계정등록
    public function RegistDeptUser($CorpNum, $deptUserID, $deptUserPWD, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($deptUserID)) {
            throw new PopbillException('홈택스 부서사용자 계정 아이디가 입력되지 않았습니다.');
        }
        if($this->isNullOrEmpty($deptUserPWD)) {
            throw new PopbillException('홈택스 부서사용자 계정 비밀번호가 입력되지 않았습니다.');
        }

        $Request = new HTTIRegistDeptUserRequest();
        $Request->id = $deptUserID;
        $Request->pwd = $deptUserPWD;
        $postdata = json_encode($Request);

        return $this->executeCURL('/HomeTax/Taxinvoice/DeptUser', $CorpNum, $UserID, true, null, $postdata);
    }

    // 부서사용자 등록정보 확인
    public function CheckDeptUser($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        
        return $this->executeCURL('/HomeTax/Taxinvoice/DeptUser', $CorpNum);
    }

    // 부서사용자 로그인 테스트
    public function CheckLoginDeptUser($CorpNum) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        
        return $this->executeCURL('/HomeTax/Taxinvoice/DeptUser/Check', $CorpNum);
    }

    // 부서사용자 등록정보 삭제
    public function DeleteDeptUser($CorpNum, $UserID = null) {
        if($this->isNullOrEmpty($CorpNum)) {
            throw new PopbillException('팝빌회원 사업자번호가 입력되지 않았습니다.');
        }
        
        return $this->executeCURL('/HomeTax/Taxinvoice/DeptUser', $CorpNum, $UserID, true, 'DELETE', null);
    }
}

class HTTIFlatRate
{
    public $referenceID;
    public $contractDT;
    public $useEndDate;
    public $baseDate;
    public $state;
    public $closeRequestYN;
    public $useRestrictYN;
    public $closeOnExpired;
    public $unPaidYN;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->referenceID) ? $this->referenceID = $jsonInfo->referenceID : null;
        isset ($jsonInfo->contractDT) ? $this->contractDT = $jsonInfo->contractDT : null;
        isset ($jsonInfo->useEndDate) ? $this->useEndDate = $jsonInfo->useEndDate : null;
        isset ($jsonInfo->baseDate) ? $this->baseDate = $jsonInfo->baseDate : null;
        isset ($jsonInfo->state) ? $this->state = $jsonInfo->state : null;
        isset ($jsonInfo->closeRequestYN) ? $this->closeRequestYN = $jsonInfo->closeRequestYN : null;
        isset ($jsonInfo->useRestrictYN) ? $this->useRestrictYN = $jsonInfo->useRestrictYN : null;
        isset ($jsonInfo->closeOnExpired) ? $this->closeOnExpired = $jsonInfo->closeOnExpired : null;
        isset ($jsonInfo->unPaidYN) ? $this->unPaidYN = $jsonInfo->unPaidYN : null;
    }
}

class HTTaxinvoiceXML
{
    public $ResultCode;
    public $Message;
    public $retObject;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->ResultCode) ? $this->ResultCode = $jsonInfo->ResultCode : null;
        isset ($jsonInfo->Message) ? $this->Message = $jsonInfo->Message : null;
        isset ($jsonInfo->retObject) ? $this->retObject = $jsonInfo->retObject : null;
    }
}

class HTTaxinvoice
{
    public $writeDate;
    public $issueDT;
    public $invoiceType;
    public $taxType;
    public $taxTotal;
    public $supplyCostTotal;
    public $totalAmount;
    public $purposeType;
    public $serialNum;
    public $cash;
    public $chkBill;
    public $credit;
    public $note;
    public $remark1;
    public $remark2;
    public $remark3;
    public $ntsconfirmNum;

    public $modifyCode;
    public $orgNTSConfirmNum;

    public $invoicerCorpNum;
    public $invoicerMgtKey;
    public $invoicerTaxRegID;
    public $invoicerCorpName;
    public $invoicerCEOName;
    public $invoicerAddr;
    public $invoicerBizType;
    public $invoicerBizClass;
    public $invoicerContactName;
    public $inovicerDeptaName;
    public $invoicerTEL;
    public $invoicerEmail;

    public $invoiceeCorpNum;
    public $invoiceeType;
    public $invoiceeMgtKey;
    public $invoiceeTaxRegID;
    public $invoiceeCorpName;
    public $invoiceeCEOName;
    public $invoiceeAddr;
    public $invoiceeBizType;
    public $invoiceeBizClass;
    public $invoiceeContactName1;
    public $invoiceeDeptName1;
    public $invoiceeTEL1;
    public $invoiceeEmail1;
    public $invoiceeContactName2;
    public $invoiceeDeptName2;
    public $invoiceeTEL2;
    public $invoiceeEmail2;

    public $trusteeCorpNum;
    public $trusteeMgtKey;
    public $trusteeTaxRegID;
    public $trusteeCorpName;
    public $trusteeCEOName;
    public $trusteeAddr;
    public $trusteeBizType;
    public $trusteeBizClass;
    public $trusteeContactName;
    public $trusteeDeptName;
    public $trusteeTEL;
    public $trusteeEmail;

    public $detailList;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->writeDate) ? $this->writeDate = $jsonInfo->writeDate : null;
        isset ($jsonInfo->issueDT) ? $this->issueDT = $jsonInfo->issueDT : null;
        isset ($jsonInfo->invoiceType) ? $this->invoiceType = $jsonInfo->invoiceType : null;
        isset ($jsonInfo->taxType) ? $this->taxType = $jsonInfo->taxType : null;
        isset ($jsonInfo->taxTotal) ? $this->taxTotal = $jsonInfo->taxTotal : null;
        isset ($jsonInfo->supplyCostTotal) ? $this->supplyCostTotal = $jsonInfo->supplyCostTotal : null;
        isset ($jsonInfo->totalAmount) ? $this->totalAmount = $jsonInfo->totalAmount : null;
        isset ($jsonInfo->purposeType) ? $this->purposeType = $jsonInfo->purposeType : null;
        isset ($jsonInfo->serialNum) ? $this->serialNum = $jsonInfo->serialNum : null;
        isset ($jsonInfo->cash) ? $this->cash = $jsonInfo->cash : null;
        isset ($jsonInfo->chkBill) ? $this->chkBill = $jsonInfo->chkBill : null;
        isset ($jsonInfo->credit) ? $this->credit = $jsonInfo->credit : null;
        isset ($jsonInfo->note) ? $this->note = $jsonInfo->note : null;
        isset ($jsonInfo->remark1) ? $this->remark1 = $jsonInfo->remark1 : null;
        isset ($jsonInfo->remark2) ? $this->remark2 = $jsonInfo->remark2 : null;
        isset ($jsonInfo->remark3) ? $this->remark3 = $jsonInfo->remark3 : null;
        isset ($jsonInfo->ntsconfirmNum) ? $this->ntsconfirmNum = $jsonInfo->ntsconfirmNum : null;

        isset ($jsonInfo->invoicerCorpNum) ? $this->invoicerCorpNum = $jsonInfo->invoicerCorpNum : null;
        isset ($jsonInfo->invoicerMgtKey) ? $this->invoicerMgtKey = $jsonInfo->invoicerMgtKey : null;
        isset ($jsonInfo->invoicerTaxRegID) ? $this->invoicerTaxRegID = $jsonInfo->invoicerTaxRegID : null;
        isset ($jsonInfo->invoicerCorpName) ? $this->invoicerCorpName = $jsonInfo->invoicerCorpName : null;
        isset ($jsonInfo->invoicerCEOName) ? $this->invoicerCEOName = $jsonInfo->invoicerCEOName : null;
        isset ($jsonInfo->invoicerAddr) ? $this->invoicerAddr = $jsonInfo->invoicerAddr : null;
        isset ($jsonInfo->invoicerBizType) ? $this->invoicerBizType = $jsonInfo->invoicerBizType : null;
        isset ($jsonInfo->invoicerBizClass) ? $this->invoicerBizClass = $jsonInfo->invoicerBizClass : null;
        isset ($jsonInfo->invoicerContactName) ? $this->invoicerContactName = $jsonInfo->invoicerContactName : null;
        isset ($jsonInfo->invoicerDeptName) ? $this->invoicerDeptName = $jsonInfo->invoicerDeptName : null;
        isset ($jsonInfo->invoicerTEL) ? $this->invoicerTEL = $jsonInfo->invoicerTEL : null;
        isset ($jsonInfo->invoicerEmail) ? $this->invoicerEmail = $jsonInfo->invoicerEmail : null;

        isset ($jsonInfo->invoiceeCorpNum) ? $this->invoiceeCorpNum = $jsonInfo->invoiceeCorpNum : null;
        isset ($jsonInfo->invoiceeType) ? $this->invoiceeType = $jsonInfo->invoiceeType : null;
        isset ($jsonInfo->invoiceeMgtKey) ? $this->invoiceeMgtKey = $jsonInfo->invoiceeMgtKey : null;
        isset ($jsonInfo->invoiceeTaxRegID) ? $this->invoiceeTaxRegID = $jsonInfo->invoiceeTaxRegID : null;
        isset ($jsonInfo->invoiceeCorpName) ? $this->invoiceeCorpName = $jsonInfo->invoiceeCorpName : null;
        isset ($jsonInfo->invoiceeCEOName) ? $this->invoiceeCEOName = $jsonInfo->invoiceeCEOName : null;
        isset ($jsonInfo->invoiceeAddr) ? $this->invoiceeAddr = $jsonInfo->invoiceeAddr : null;
        isset ($jsonInfo->invoiceeBizType) ? $this->invoiceeBizType = $jsonInfo->invoiceeBizType : null;
        isset ($jsonInfo->invoiceeBizClass) ? $this->invoiceeBizClass = $jsonInfo->invoiceeBizClass : null;
        isset ($jsonInfo->invoiceeContactName1) ? $this->invoiceeContactName1 = $jsonInfo->invoiceeContactName1 : null;
        isset ($jsonInfo->invoiceeDeptName1) ? $this->invoiceeDeptName1 = $jsonInfo->invoiceeDeptName1 : null;
        isset ($jsonInfo->invoiceeTEL1) ? $this->invoiceeTEL1 = $jsonInfo->invoiceeTEL1 : null;
        isset ($jsonInfo->invoiceeEmail1) ? $this->invoiceeEmail1 = $jsonInfo->invoiceeEmail1 : null;
        isset ($jsonInfo->invoiceeContactName2) ? $this->invoiceeContactName2 = $jsonInfo->invoiceeContactName2 : null;
        isset ($jsonInfo->invoiceeDeptName2) ? $this->invoiceeDeptName2 = $jsonInfo->invoiceeDeptName2 : null;
        isset ($jsonInfo->invoiceeTEL2) ? $this->invoiceeTEL2 = $jsonInfo->invoiceeTEL2 : null;
        isset ($jsonInfo->invoiceeEmail2) ? $this->invoiceeEmail2 = $jsonInfo->invoiceeEmail2 : null;

        isset ($jsonInfo->trusteeCorpNum) ? $this->trusteeCorpNum = $jsonInfo->trusteeCorpNum : null;
        isset ($jsonInfo->trusteeMgtKey) ? $this->trusteeMgtKey = $jsonInfo->trusteeMgtKey : null;
        isset ($jsonInfo->trusteeTaxRegID) ? $this->trusteeTaxRegID = $jsonInfo->trusteeTaxRegID : null;
        isset ($jsonInfo->trusteeCorpName) ? $this->trusteeCorpName = $jsonInfo->trusteeCorpName : null;
        isset ($jsonInfo->trusteeCEOName) ? $this->trusteeCEOName = $jsonInfo->trusteeCEOName : null;
        isset ($jsonInfo->trusteeAddr) ? $this->trusteeAddr = $jsonInfo->trusteeAddr : null;
        isset ($jsonInfo->trusteeBizType) ? $this->trusteeBizType = $jsonInfo->trusteeBizType : null;
        isset ($jsonInfo->trusteeBizClass) ? $this->trusteeBizClass = $jsonInfo->trusteeBizClass : null;
        isset ($jsonInfo->trusteeContactName) ? $this->trusteeContactName = $jsonInfo->trusteeContactName : null;
        isset ($jsonInfo->trusteeDeptName) ? $this->trusteeDeptName = $jsonInfo->trusteeDeptName : null;
        isset ($jsonInfo->trusteeTEL) ? $this->trusteeTEL = $jsonInfo->trusteeTEL : null;
        isset ($jsonInfo->trusteeEmail) ? $this->trusteeEmail = $jsonInfo->trusteeEmail : null;

        isset ($jsonInfo->modifyCode) ? $this->modifyCode = $jsonInfo->modifyCode : null;
        isset ($jsonInfo->orgNTSConfirmNum) ? $this->orgNTSConfirmNum = $jsonInfo->orgNTSConfirmNum : null;

        $DetailList = array();
        for ($i = 0; $i < Count($jsonInfo->detailList); $i++) {
            $TaxinvoiceDetail = new HTTaxinvoiceDetail();
            $TaxinvoiceDetail->fromJsonInfo($jsonInfo->detailList[$i]);
            $DetailList[$i] = $TaxinvoiceDetail;
        }
        $this->detailList = $DetailList;
    }
}

class HTTaxinvoiceDetail
{
    public $serialNum;
    public $purchaseDT;
    public $itemName;
    public $spec;
    public $qty;
    public $unitCost;
    public $supplyCost;
    public $tax;
    public $remark;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->serialNum) ? $this->serialNum = $jsonInfo->serialNum : null;
        isset ($jsonInfo->purchaseDT) ? $this->purchaseDT = $jsonInfo->purchaseDT : null;
        isset ($jsonInfo->itemName) ? $this->itemName = $jsonInfo->itemName : null;
        isset ($jsonInfo->spec) ? $this->spec = $jsonInfo->spec : null;
        isset ($jsonInfo->qty) ? $this->qty = $jsonInfo->qty : null;
        isset ($jsonInfo->unitCost) ? $this->unitCost = $jsonInfo->unitCost : null;
        isset ($jsonInfo->supplyCost) ? $this->supplyCost = $jsonInfo->supplyCost : null;
        isset ($jsonInfo->tax) ? $this->tax = $jsonInfo->tax : null;
        isset ($jsonInfo->remark) ? $this->remark = $jsonInfo->remark : null;
    }
}

class HTTaxinvoieSummary
{
    public $count;
    public $supplyCostTotal;
    public $taxTotal;
    public $amountTotal;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->count) ? $this->count = $jsonInfo->count : null;
        isset ($jsonInfo->supplyCostTotal) ? $this->supplyCostTotal = $jsonInfo->supplyCostTotal : null;
        isset ($jsonInfo->taxTotal) ? $this->taxTotal = $jsonInfo->taxTotal : null;
        isset ($jsonInfo->amountTotal) ? $this->amountTotal = $jsonInfo->amountTotal : null;
    }
}


class HTTaxinvoiceSearch
{
    public $code;
    public $message;
    public $total;
    public $perPage;
    public $pageNum;
    public $pageCount;
    public $list;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->code) ? $this->code = $jsonInfo->code : null;
        isset ($jsonInfo->message) ? $this->message = $jsonInfo->message : null;
        isset ($jsonInfo->total) ? $this->total = $jsonInfo->total : null;
        isset ($jsonInfo->perPage) ? $this->perPage = $jsonInfo->perPage : null;
        isset ($jsonInfo->pageNum) ? $this->pageNum = $jsonInfo->pageNum : null;
        isset ($jsonInfo->pageCount) ? $this->pageCount = $jsonInfo->pageCount : null;

        $TaxinvoiceList = array();
        for ($i = 0; $i < Count($jsonInfo->list); $i++) {
            $TaxinvoiceAbbr = new HTTaxinvoiceAbbr();
            $TaxinvoiceAbbr->fromJsonInfo($jsonInfo->list[$i]);
            $TaxinvoiceList[$i] = $TaxinvoiceAbbr;
        }
        $this->list = $TaxinvoiceList;
    }
}

class HTTaxinvoiceAbbr
{
    public $ntsconfirmNum;
    public $writeDate;
    public $issueDate;
    public $sendDate;
    public $taxType;
    public $purposeType;
    public $supplyCostTotal;
    public $taxTotal;
    public $totalAmount;
    public $remark1;

    public $modifyYN;
    public $orgNTSConfirmNum;

    public $purchaseDate;
    public $itemName;
    public $spec;
    public $qty;
    public $unitCost;
    public $supplyCost;
    public $tax;
    public $remark;

    public $invoicerCorpNum;
    public $invoicerTaxRegID;
    public $invoicerCorpName;
    public $invoicerCEOName;
    public $invoicerEmail;

    public $inoviceeCorpNum;
    public $invoiceeType;
    public $invoiceeTaxRegID;
    public $invoiceeCorpName;
    public $invoiceeCEOName;
    public $invoiceeEmail1;
    public $invoiceeEmail2;

    public $trusteeCorpNum;
    public $trusteeTaxRegID;
    public $trusteeCorpName;
    public $trusteeCEOName;
    public $trusteeEmail;

    /*
    * 매출/매입 구분 필드 추가 (2017/08/29)
    */
    public $invoiceType;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->ntsconfirmNum) ? $this->ntsconfirmNum = $jsonInfo->ntsconfirmNum : null;
        isset ($jsonInfo->writeDate) ? $this->writeDate = $jsonInfo->writeDate : null;
        isset ($jsonInfo->issueDate) ? $this->issueDate = $jsonInfo->issueDate : null;
        isset ($jsonInfo->sendDate) ? $this->sendDate = $jsonInfo->sendDate : null;
        isset ($jsonInfo->taxType) ? $this->taxType = $jsonInfo->taxType : null;
        isset ($jsonInfo->purposeType) ? $this->purposeType = $jsonInfo->purposeType : null;
        isset ($jsonInfo->supplyCostTotal) ? $this->supplyCostTotal = $jsonInfo->supplyCostTotal : null;
        isset ($jsonInfo->taxTotal) ? $this->taxTotal = $jsonInfo->taxTotal : null;
        isset ($jsonInfo->totalAmount) ? $this->totalAmount = $jsonInfo->totalAmount : null;
        isset ($jsonInfo->remark1) ? $this->remark1 = $jsonInfo->remark1 : null;

        isset ($jsonInfo->modifyYN) ? $this->modifyYN = $jsonInfo->modifyYN : null;
        isset ($jsonInfo->orgNTSConfirmNum) ? $this->orgNTSConfirmNum = $jsonInfo->orgNTSConfirmNum : null;

        isset ($jsonInfo->invoicerCorpNum) ? $this->invoicerCorpNum = $jsonInfo->invoicerCorpNum : null;
        isset ($jsonInfo->invoicerTaxRegID) ? $this->invoicerTaxRegID = $jsonInfo->invoicerTaxRegID : null;
        isset ($jsonInfo->invoicerCorpName) ? $this->invoicerCorpName = $jsonInfo->invoicerCorpName : null;
        isset ($jsonInfo->invoicerCEOName) ? $this->invoicerCEOName = $jsonInfo->invoicerCEOName : null;
        isset ($jsonInfo->invoicerEmail) ? $this->invoicerEmail = $jsonInfo->invoicerEmail : null;

        isset ($jsonInfo->invoiceeCorpNum) ? $this->invoiceeCorpNum = $jsonInfo->invoiceeCorpNum : null;
        isset ($jsonInfo->invoiceeType) ? $this->invoiceeType = $jsonInfo->invoiceeType : null;
        isset ($jsonInfo->invoiceeTaxRegID) ? $this->invoiceeTaxRegID = $jsonInfo->invoiceeTaxRegID : null;
        isset ($jsonInfo->invoiceeCorpName) ? $this->invoiceeCorpName = $jsonInfo->invoiceeCorpName : null;
        isset ($jsonInfo->invoiceeCEOName) ? $this->invoiceeCEOName = $jsonInfo->invoiceeCEOName : null;
        isset ($jsonInfo->invoiceeEmail1) ? $this->invoiceeEmail1 = $jsonInfo->invoiceeEmail1 : null;
        isset ($jsonInfo->invoiceeEmail2) ? $this->invoiceeEmail2 = $jsonInfo->invoiceeEmail2 : null;

        isset ($jsonInfo->purchaseDate) ? $this->purchaseDate = $jsonInfo->purchaseDate : null;
        isset ($jsonInfo->itemName) ? $this->itemName = $jsonInfo->itemName : null;
        isset ($jsonInfo->spec) ? $this->spec = $jsonInfo->spec : null;
        isset ($jsonInfo->qty) ? $this->qty = $jsonInfo->qty : null;
        isset ($jsonInfo->unitCost) ? $this->unitCost = $jsonInfo->unitCost : null;
        isset ($jsonInfo->supplyCost) ? $this->supplyCost = $jsonInfo->supplyCost : null;
        isset ($jsonInfo->tax) ? $this->tax = $jsonInfo->tax : null;
        isset ($jsonInfo->remark) ? $this->remark = $jsonInfo->remark : null;

        isset ($jsonInfo->trusteeCorpNum) ? $this->trusteeCorpNum = $jsonInfo->trusteeCorpNum : null;
        isset ($jsonInfo->trusteeTaxRegID) ? $this->trusteeTaxRegID = $jsonInfo->trusteeTaxRegID : null;
        isset ($jsonInfo->trusteeCorpName) ? $this->trusteeCorpName = $jsonInfo->trusteeCorpName : null;
        isset ($jsonInfo->trusteeCEOName) ? $this->trusteeCEOName = $jsonInfo->trusteeCEOName : null;
        isset ($jsonInfo->trusteeEmail) ? $this->trusteeCEOName = $jsonInfo->trusteeCEOName : null;

        isset ($jsonInfo->invoiceType) ? $this->invoiceType = $jsonInfo->invoiceType : null;
    }
}

class HTTIJobState
{
    public $jobID;
    public $jobState;
    public $queryType;
    public $queryDateType;
    public $queryStDate;
    public $queryEnDate;
    public $errorCode;
    public $errorReason;
    public $jobStartDT;
    public $jobEndDT;
    public $collectCount;
    public $regDT;

    public function fromJsonInfo($jsonInfo)
    {
        isset($jsonInfo->jobID) ? $this->jobID = $jsonInfo->jobID : null;
        isset($jsonInfo->jobState) ? $this->jobState = $jsonInfo->jobState : null;
        isset($jsonInfo->queryType) ? $this->queryType = $jsonInfo->queryType : null;
        isset($jsonInfo->queryDateType) ? $this->queryDateType = $jsonInfo->queryDateType : null;
        isset($jsonInfo->queryStDate) ? $this->queryStDate = $jsonInfo->queryStDate : null;
        isset($jsonInfo->queryEnDate) ? $this->queryEnDate = $jsonInfo->queryEnDate : null;
        isset($jsonInfo->errorCode) ? $this->errorCode = $jsonInfo->errorCode : null;
        isset($jsonInfo->errorReason) ? $this->errorReason = $jsonInfo->errorReason : null;
        isset($jsonInfo->jobStartDT) ? $this->jobStartDT = $jsonInfo->jobStartDT : null;
        isset($jsonInfo->jobEndDT) ? $this->jobEndDT = $jsonInfo->jobEndDT : null;
        isset($jsonInfo->collectCount) ? $this->collectCount = $jsonInfo->collectCount : null;
        isset($jsonInfo->regDT) ? $this->regDT = $jsonInfo->regDT : null;
    }
}

class HTTIKeyType
{
    const SELL = 'SELL';
    const BUY = 'BUY';
    const TRUSTEE = 'TRUSTEE';
}

class HTTIRegistDeptUserRequest
{
    public $id;
    public $pwd;

    public function fromJsonInfo($jsonInfo)
    {
        isset ($jsonInfo->id) ? $this->id = $jsonInfo->id : null;
        isset ($jsonInfo->pwd) ? $this->pwd = $jsonInfo->pwd : null;
    }
}

?>
