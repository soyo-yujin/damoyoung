<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "다모영 테스트서버";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "postUser":

            $androidNum= $req->androidNum;

            if(strlen($androidNum) < 1)
            {
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "안드로이드 기기 번호를 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            else if(strlen($androidNum) > 0)
            {

                $is_exist_user = androidCheck($androidNum);
                if ($is_exist_user == 1) {
                    $jwt = getJwt($androidNum);
                    http_response_code(200);
                    $res->result->jwt = $jwt;
                    $res->isSuccess = TRUE;
                    $res->code = 101;
                    $res->message = "이미 가입되어있는 유저로 토큰값을 발행합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else if ($is_exist_user == 0)
                {
                    http_response_code(200);
                    $jwt = getJWToken($androidNum, JWT_SECRET_KEY);
                    postUser($androidNum,$jwt);
                    $res->result->jwt = $jwt;
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "유저정보 저장을 성공했습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

            }
            break;


        case "postMark":

            $name = $req->name;

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];

            $userNo = convert_to_userNo($androidNum);
//            echo $androidNum;
//            echo $userNo;

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {
//                echo "$name";

                if(strlen($name) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 102;
                    $res->message = "북마크 이름을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                postMark($name, $userNo);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "북마크 생성을 성공했습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;

        case "postWord":

            $bookmark = $req->bookmark;
            $type = $req->type;
            $word = $req->word;
            $example = $req->example;

            $count = 0;
            $int = 0;
            foreach($bookmark as $markNum => $value)
            {
                $markNum = $value->markNum;
                $markResult[$count++] = $markNum;
            }

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];

            $patternNum = "/^[0-9]+$/";

            $userNo = convert_to_userNo($androidNum);

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1) {
                if (count($bookmark) < 1) {
                    $res->isSuccess = FALSE;
                    $res->code = 103;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (strlen($type) < 1) {
                    $res->isSuccess = FALSE;
                    $res->code = 104;
                    $res->message = "타입 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                foreach ($markResult as $value) {
                    $bookmark_exist = bookmark_exist($userNo, $value);

                    if ($bookmark_exist == 0) {
                        $res->isSuccess = FALSE;
                        $res->code = 400;
                        $res->message = "유효한 북마크 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    if (!preg_match($patternNum, $value)) {

                        $res->isSuccess = false;
                        $res->code = 300;
                        $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                }

                $wordNum = 0;
                $exampleNum = 1;

                if ($type == $wordNum) {// 단어저장
                    if (strlen($word) < 1) {
                        $res->isSuccess = FALSE;
                        $res->code = 105;
                        $res->message = "단어를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $is_exist_word = is_exist_word($word);
                    if ($is_exist_word == 1) {
                        $wordNo = select_master_word($word);
                    } else if ($is_exist_word == 0) {
                        $wordNo = master_word($word);
                    }

                    foreach ($markResult as $row)
                    {
                        $is_exist_word_mark = wordcheck($wordNo, $row);
                        if($is_exist_word_mark == 1)
                        {
                            $int = 1;
                        }
                        else if ($is_exist_word_mark == 0)
                        {
                            saveWord($wordNo, $row);
                            $int = 1;
                        }
                    }

                    if($int == 1)
                    {
                        $res->isSuccess = TRUE;
                        $res->code = 100;
                        $res->message = "단어 저장 및 예문 저장을 성공하였습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                    }

                } else if ($type == $exampleNum) {// 예문 저장
                    if (strlen($word) < 1) {
                        $res->isSuccess = FALSE;
                        $res->code = 105;
                        $res->message = "단어를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    if (strlen($example) < 1) {
                        $res->isSuccess = FALSE;
                        $res->code = 106;
                        $res->message = "예문을 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                    $is_exist_word = is_exist_word($word); //마스터 테이블 단어 체킹
                    if ($is_exist_word == 1) {
                        $wordNo = select_master_word($word);
                    } else if ($is_exist_word == 0) {
                        $wordNo = master_word($word);
                    }

                    foreach ($markResult as $row)
                    {
                        $is_exist_word_mark = wordcheck($wordNo, $row);

                        if($is_exist_word_mark == 1)
                        {
                            $int = 1;
                            saveExample_without_word($wordNo, $example, $row);
                        }
                        else if ($is_exist_word_mark == 0)
                        {
                            $int = 1;
                            saveExample($wordNo, $example, $row);
                        }
                    }
                    if($int == 1)
                    {
                        $res->isSuccess = TRUE;
                        $res->code = 100;
                        $res->message = "단어 저장 및 예문 저장을 성공하였습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                    }
                }
            }
            break;

        case "getBookmark":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];

            $userNo = convert_to_userNo($androidNum);

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {
                $res->result = getbookmark($userNo);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "북마크 목록을 성공적으로 조회하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;

        case "getWord":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
            $is_memorized = $req->is_memorized;


            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {
                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 202;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($is_memorized) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 203;
                    $res->message = "암기여부를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $res->result =  getword($bookmarkNo, $is_memorized);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "북마크 단어를 성공적으로 조회하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            break;

        case "patchMark":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
            $name = $req->name;


            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {

                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 107;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($name) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 108;
                    $res->message = "묵마크 이름을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                patchBookmark($bookmarkNo, $name, $userNo);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "북마크 이름을 성공적으로 변경하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;


        case "deleteMark":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
//            $name = $req->name;


            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {

                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 109;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }


                deleteMark($userNo, $bookmarkNo);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "북마크를 성공적으로 삭제하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;

        case "deleteWord":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
            $wordList = $req->wordList;

            $count = 0;
            foreach($wordList as $wordNo => $value)
            {
                $wordNo = $value->wordNo;
                $wordResult[$count++] = $wordNo;
            }

            $patternNum = "/^[0-9]+$/";

            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {

                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 110;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $bookmarkNo))
                {

                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                foreach($wordResult as $value)
                {
                    $word_exist = word_exist($bookmarkNo, $value);
//
                    if($word_exist == 0)
                    {
                        $res->isSuccess = FALSE;
                        $res->code = 401;
                        $res->message = "유효한 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    if(strlen($value) < 1)
                    {
                        $res->isSuccess = FALSE;
                        $res->code = 111;
                        $res->message = "단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    if (!preg_match($patternNum, $value))
                    {

                        $res->isSuccess = false;
                        $res->code = 301;
                        $res->message = "숫자 형식에 맞게 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                }

                deleteWord($bookmarkNo, $wordResult);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단어를 성공적으로 삭제하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;

        case "moveWord": //암기미암기 이동

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
            $wordNo = $vars["wordNo"];
            $is_memorized = $req->is_memorized;

            $patternNum = "/^[0-9]+$/";


            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {

                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 112;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $bookmarkNo))
                {

                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $word_exist = word_exist($bookmarkNo, $wordNo);

                if($word_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 401;
                    $res->message = "유효한 단어 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($wordNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 113;
                    $res->message = "단어 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $wordNo))
                {
                    $res->isSuccess = false;
                    $res->code = 301;
                    $res->message = "숫자 형식에 맞게 단어 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($is_memorized) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 114;
                    $res->message = "암기/미암기를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                moveWord($bookmarkNo, $wordNo, $is_memorized);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단어를 성공적으로 이동하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;


        case "deleteEx":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];
            $bookmarkNo = $vars["bookmarkNo"];
            $sentenceNo = $vars["sentenceNo"];

            $patternNum = "/^[0-9]+$/";

            $userNo = convert_to_userNo($androidNum);

//            echo "$userNo";

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {
                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 115;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $bookmarkNo))
                {

                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $ex_exist = ex_exist($bookmarkNo, $sentenceNo);

                if($ex_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 401;
                    $res->message = "유효한 예문 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($sentenceNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 116;
                    $res->message = "예문 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $sentenceNo))
                {
                    $res->isSuccess = false;
                    $res->code = 301;
                    $res->message = "숫자 형식에 맞게 예문 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                deleteEx($sentenceNo);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "예문을 성공적으로 삭제하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;


        case "exportWord":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];

            $patternNum = "/^[0-9]+$/";


            $wordList = $req->wordList;
            $newbookmarkNo = $req->newbookmarkNo; // 새로운 북마크 번호
            $bookmarkNo = $req->bookmarkNo; //현재 북마크 번호


            $count = 0;

            foreach($wordList as $wordNo => $value)
            {
                $wordNo = $value->wordNo;
                $wordResult[$count++] = $wordNo;
            }

            $userNo = convert_to_userNo($androidNum);

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {
                if(count($wordList) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 117;
                    $res->message = "단어 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
//
                foreach($wordResult as  $value) {

                    if (!preg_match($patternNum, $value)) {
                        $res->isSuccess = false;
                        $res->code = 301;
                        $res->message = "숫자 형식에 맞게 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $is_already_word = wordcheck2($value, $bookmarkNo);

                    if($is_already_word == 0)
                    {
                        $res->isSuccess = FALSE;
                        $res->code = 500;
                        $res->message = "유효한 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                }
                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $bookmarkNo))
                {
                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 118;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $bookmark_check = bookmark_check($newbookmarkNo);

                if($bookmark_check == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $newbookmarkNo))
                {
                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($newbookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 119;
                    $res->message = "이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } // 검증 완료

                if($bookmarkNo == $newbookmarkNo)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 800;
                    $res->message = "같은 단어장으론 이동할 수 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
//                //----- 예문정보 저장, 단어 정보 저장
                $int = 0;
                foreach($wordResult as $value)
                {
                    $infornation = save_word_information($value, $bookmarkNo);
                    $master_wordNo = $infornation['wordNo'];
                    $sentenList = $infornation['sentence'];
                    $no = $infornation['exNo'];

                    if($no == 0)
                    {
                        $type = 1;
                    }
                    else if($no == 1)
                    {
                        $type = 2;
                    }

                    $is_already_word = is_already_word($master_wordNo, $newbookmarkNo); //단어자체가 새로운 북마크에 있는지

                    if ($is_already_word == 0) //단어가 없을때
                    {

                        update_move_word($value, $newbookmarkNo, $bookmarkNo);
                        $int = 1;
                    } else if ($is_already_word == 1) //단어가 있을때
                    {
                        //단어 이미 있음
                        $is_already_ex = is_already_ex($value);
                        if ($is_already_ex == 0) //예문이없을때
                        {
                            if ($type == 1) {
                                $int = 1;
                            }

                            if ($type == 2) {
                                move_word_is_exist_word($master_wordNo, $newbookmarkNo, $bookmarkNo, $sentenList);
                                $int = 1;
                            }

                        } else if ($is_already_ex == 1)
                        {
                            //새로운 북마크에 예문이 있을때
                            move_word_is_exist_ex($newbookmarkNo, $bookmarkNo, $sentenList, $value, $master_wordNo);
                            $int = 1;
                        }
                    }

                }

                if ($int == 1)
                {
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "단어를 성공적으로 이동하였습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                }
            }

            break;

        case "copyWord":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            $result = isValidHeader($jwt, JWT_SECRET_KEY);
            $isintval = $result['intval'];
            $androidNum = $result['android_id'];

            $patternNum = "/^[0-9]+$/";

            $wordList = $req->wordList; //word 테이블 번호
            $newbookmarkNo = $req->newbookmarkNo; // 새로운 북마크 번호
            $bookmarkNo = $req->bookmarkNo; //현재 북마크 번호

            $count = 0;
            $int = 1;

            foreach($wordList as $wordNo => $value)
            {
                $wordNo = $value->wordNo;
                $wordResult[$count++] = $wordNo;
            }

            $userNo = convert_to_userNo($androidNum);

            if ($isintval == 0)
            {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            else if($isintval == 1)
            {

                if(count($wordList) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 120;
                    $res->message = "단어 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                foreach($wordResult as  $value)
                {
//                    echo json_encode($value);
                    if (!preg_match($patternNum, $value)) {
                        $res->isSuccess = false;
                        $res->code = 301;
                        $res->message = "숫자 형식에 맞게 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }

                    $is_already_word = wordcheck2($value, $bookmarkNo);

                    if($is_already_word == 0)
                    {
                        $res->isSuccess = FALSE;
                        $res->code = 500;
                        $res->message = "유효한 단어 번호를 입력해주세요";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                }

                $bookmark_exist = bookmark_exist($userNo, $bookmarkNo);

                if($bookmark_exist == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $bookmarkNo))
                {
                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($bookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 121;
                    $res->message = "북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $bookmark_check = bookmark_check($newbookmarkNo);

                if($bookmark_check == 0)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 400;
                    $res->message = "유효한 이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if (!preg_match($patternNum, $newbookmarkNo))
                {
                    $res->isSuccess = false;
                    $res->code = 300;
                    $res->message = "숫자 형식에 맞게 이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                if(strlen($newbookmarkNo) < 1)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 122;
                    $res->message = "이동할 북마크 번호를 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } // 검증 완료


                if($bookmarkNo == $newbookmarkNo)
                {
                    $res->isSuccess = FALSE;
                    $res->code = 800;
                    $res->message = "같은 단어장으론 복사할 수 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                foreach($wordResult as $value) //단어 번호 value
                {
                    $infornation = save_word_information($value, $bookmarkNo); //기존단어
                    $master_wordNo = $infornation['wordNo'];
                    $sentenList = $infornation['sentence'];
                    $no = $infornation['exNo'];

                    if($no == 0) //단어만 있을때
                    {
//                        echo "aaa";
                        $type = 1;
                    }
                    else if($no == 1) // 단어와 예문 둘다 있을때
                    {
//                        echo "bbb";
                        $type = 2;
                    }

                    $is_already_word = is_already_word($master_wordNo, $newbookmarkNo);

                    if ($is_already_word == 0)//단어가 없을때
                    {
                        if($type == 1)
                        {
//                           echo "$newbookmarkNo, $master_wordNo";
                            write_copy_word($newbookmarkNo, $master_wordNo);
                            $int = 1;
                        }
                        else if($type == 2)
                        {
                            write_copy_word_with_ex($newbookmarkNo, $master_wordNo, $sentenList);
                            $int = 1;
                        }

                    } else if ($is_already_word == 1) //단어가 있을떄
                    {

                        $is_already_ex = is_already_ex($value);
                        if ($is_already_ex == 0) //예문이없을때
                        {
                            if($type == 2) {
                                copyExample($newbookmarkNo, $sentenList, $master_wordNo);
                                $int = 1;
                            }
                        } else if ($is_already_ex == 1) //예문이 있을때
                        {
                            if($type == 2) {
                                copy_add_Example($newbookmarkNo, $sentenList, $master_wordNo);
                                $int = 1;
                            }
                        }
                    }
                }

                if ($int == 1)
                {
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "단어를 성공적으로 복사하였습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                }
            }

            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
