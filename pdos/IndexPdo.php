<?php

function androidCheck($androidNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from user where android_id = ?)as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$androidNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function postUser($androidNum,$jwt)
{
    try {

        $no = (int)0;
        $is_registered = date("Y-m-d H:i:s");
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO user (no, android_id, registered_at, jwt) VALUES (?,?,?,?);;";

        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute([$no, $androidNum, $is_registered,$jwt]);


        $st = null;
        $pdo = null;


        $name = "나의 리스트";
        $no = (int)0;
        $is_registered = date("Y-m-d H:i:s");
        $is_deleted = (string)'N';
        $pdo = pdoSqlConnect();
        $query =
            "select no from  user where registered_at = ?;";
        $st2 = $pdo->prepare($query);
        $st2->execute([$is_registered]);
        $st2->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st2->fetchAll();

        $st2 = null;
        $pdo = null;

        $userNo = $res[0]['no'];


        $pdo = pdoSqlConnect();
        $postMark = "INSERT INTO bookmark (no, title, is_deleted, registered_at, user_no) VALUES (?,?,?,?,?);";
        $st3 = $pdo->prepare($postMark);

        $pdo->beginTransaction();
        $st3->execute([$no, $name, $is_deleted, $is_registered, $userNo]);


        $pdo->commit();
//
        $st3 = null;
        $pdo = null;
//
        return 1;



//
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}


function getJwt($androidNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT jwt FROM user where android_id = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$androidNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['jwt'];
}

function postMark($name, $userNo)
{
//    echo "userNO = $userNo";
    $is_deleted = (string)'N';
    $no = (int)0;
    $is_registered = date("Y-m-d H:i:s");

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO bookmark (no, title, is_deleted, registered_at, user_no) VALUES (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$no, $name, $is_deleted, $is_registered, $userNo]);

    $st = null;
    $pdo = null;
}

function wordcheck($wordNo, $row)
{
    $is_deleted = 'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark_relations_word where word_no = ? and is_deleted = ? and bookmark_no = ?)as exist;";

    $st = $pdo->prepare($query);
    $st->bindValue(1, $wordNo);
    $st->bindValue(2, $is_deleted);
    $st->bindValue(3, $row);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function saveWord($wordNo, $row) //단어저장만
{
    $int = (int)0;
    $is_deleted = (string)'N';
    $no = (int)0;
    $is_registered = date("Y-m-d H:i:s");
    $is_memorized = (string)'N';


    $pdo = pdoSqlConnect();
    $query = "INSERT INTO bookmark_relations_word (no, is_memorized, word_no, is_deleted, bookmark_no, registered_at) VALUES (?,?,?,?,?,?);";

    $st = $pdo->prepare($query);

    $st->bindValue($int + 1, $no);
    $st->bindValue($int + 2, $is_memorized);
    $st->bindValue($int + 3, $wordNo);
    $st->bindValue($int + 4, $is_deleted);
    $st->bindValue($int + 5, $row);
    $st->bindValue($int + 6, $is_registered);

    $st->execute();

    $st = null;
    $pdo = null;
}

function saveExample_without_word($wordNo, $example, $row)
{
    try {
//    echo "$row";
        $is_deleted = (string)'N';
        $no = (int)0;
        $pdo = pdoSqlConnect();
        $query = "select no from bookmark_relations_word where word_no = ? and is_deleted = ? and bookmark_no = ?;";

        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute([$wordNo, $is_deleted, $row]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        $bookmark_word_no = $res[0]['no'];

        $pdo = pdoSqlConnect();
        $query = "INSERT INTO bookmark_relations_example (no, word_no, example, is_deleted) VALUES (?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$no, $bookmark_word_no, $example, $is_deleted]);

        $st = null;
        $pdo = null;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}

function saveExample($wordNo, $example, $row) //예문저장도
{
    try {
        $is_deleted = (string)'N';
        $no = (int)0;
        $is_registered = date("Y-m-d H:i:s");
        $is_memorized = (string)'N';
        $int = (int)0;

        $pdo = pdoSqlConnect();
        $query = "INSERT INTO bookmark_relations_word (no, is_memorized, word_no, is_deleted, bookmark_no, registered_at) VALUES (?,?,?,?,?,?);";

        $st = $pdo->prepare($query);

        $st->bindValue($int + 1, $no);
        $st->bindValue($int + 2, $is_memorized);
        $st->bindValue($int + 3, $wordNo);
        $st->bindValue($int + 4, $is_deleted);
        $st->bindValue($int + 5, $row);
        $st->bindValue($int + 6, $is_registered);

        $st->execute();

        $st = null;
        $pdo = null;

        $pdo2 = pdoSqlConnect();
        $query2 = "select no from bookmark_relations_word where is_deleted = ? and registered_at = ? order by registered_at desc;";

        $st2 = $pdo2->prepare($query2);
        //    $st->execute([$param,$param]);
        $st2->execute([$is_deleted, $is_registered]);
        $st2->setFetchMode(PDO::FETCH_ASSOC);
        $res2 = $st2->fetchAll();


        $st2 = null;
        $pdo2 = null;

        foreach ($res2 as $row) {
            $wordNo = $row['no'];

            $pdo = pdoSqlConnect();
            $query = "INSERT INTO bookmark_relations_example (no, word_no, example, is_deleted) VALUES (?,?,?,?);";
            $st = $pdo->prepare($query);
            $st->execute([$no, $wordNo, $example, $is_deleted]);

            $st = null;
            $pdo = null;
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}

function getbookmark($userNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "SELECT no, title from bookmark where user_no = ? and is_deleted = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userNo, $is_deleted]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getword($bookmarkNo, $is_memorized)
{
    try {
        $result = array();
//    $is_memorized = (string)'N';
        $is_deleted = (string)'N';
        $pdo = pdoSqlConnect();
        $query = "select bookmark_relations_word.no as word_no, word_master.word
                     from bookmark_relations_word
                              inner join word_master on bookmark_relations_word.word_no = word_master.no
                     where bookmark_no = ?
                       and bookmark_relations_word.is_memorized = ?
                       and bookmark_relations_word.is_deleted = ?;";

        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute([$bookmarkNo, $is_memorized, $is_deleted]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        foreach ($res as $row) {
            $wordNo = $row['word_no'];
//
//        echo "aaa";

            $st = null;
            $pdo = null;

            $pdo = pdoSqlConnect();
            $postMark = "select no as sentence_no, example as sentence from bookmark_relations_example where word_no = ? and is_deleted = ?;";
            $st = $pdo->prepare($postMark);

            $pdo->beginTransaction();
            $st->execute([$wordNo, $is_deleted]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();


            $pdo->commit();
//
            $st = null;
            $pdo = null;

            $sentense = $res;


            $row['sentenceList'] = $sentense;
//
//        return $res;
            array_push($result, $row);

        }
//
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
    return $result;
}

function patchBookmark($bookmarkNo, $name, $userNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark
                        SET title = ?
                        WHERE bookmark.no = ? and is_deleted = ? and user_no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$name, $bookmarkNo, $is_deleted, $userNo]);
    $st = null;
    $pdo = null;
}

function deleteMark($userNo, $bookmarkNo)
{
    $is_deleted = (string)'Y';
    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark
                        SET is_deleted = ?
                        WHERE bookmark.no = ? and user_no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$is_deleted, $bookmarkNo, $userNo]);
    $st = null;
    $pdo = null;
}

function deleteWord($bookmarkNo, $wordResult)
{
    $count = count($wordResult);
    $question_marks = str_repeat(",?", $count - 1);
    $int = (int)0;
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark_relations_word SET is_deleted = CASE no
                          WHEN no THEN 'Y'
                          ELSE is_deleted
                        END
             WHERE no IN (?$question_marks) and bookmark_no = ? and is_deleted = ?;";

    $st = $pdo->prepare($query);

    foreach ($wordResult as $row => $value) {
        $st->bindValue($int + 1, $value);
        $int = $int + 1;
    }
    $st->bindValue($int + 1, $bookmarkNo);
    $st->bindValue($int + 2, $is_deleted);
    $st->execute();
    $st = null;
    $pdo = null;
}

function moveWord($bookmarkNo, $wordNo, $is_memorized)
{
    if($is_memorized == 'N') // 현재 N일때 Y 암기 상태로 변경
    {
        $is_memorized = 'Y';
    }
    else if ($is_memorized == 'Y') //현재
    {
        $is_memorized = 'N';
    }

    $is_deleted = (string)'N';

    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark_relations_word
                        SET is_memorized = ?
                        WHERE bookmark_no= ? and no= ? and is_deleted = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$is_memorized, $bookmarkNo, $wordNo, $is_deleted]);
    $st = null;
    $pdo = null;
}

function deleteEx($sentenceNo)
{
    $is_deleted = (string)'Y';
    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark_relations_example
                        SET is_deleted = ?
                        WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$is_deleted, $sentenceNo]);
    $st = null;
    $pdo = null;
}

function move_word_is_exist_word($master_wordNo, $newbookmarkNo, $bookmarkNo, $sentenList)
{
    try {
        $is_deleted = (string)'N';
        $is_deleted2 = (string)'Y';
        $no = (int)0;
        $int = (int)0;

        $pdo = pdoSqlConnect();
        $query = "select no from bookmark_relations_word where word_no = ? and bookmark_no = ? and is_deleted = ?;";

        $st = $pdo->prepare($query);

        $st->execute([$master_wordNo, $newbookmarkNo, $is_deleted]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        $wordResult = $res[0]['no']; // insert 사용

        $pdo = pdoSqlConnect();
        $query = "update bookmark_relations_word set is_deleted =? where  bookmark_no =? and is_deleted = ? and word_no = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$is_deleted2, $bookmarkNo, $is_deleted, $master_wordNo]);
        $st = null;
        $pdo = null;

        $count = count($sentenList);
        $question_marks = str_repeat(",(?,?,?,?)", $count - 1);

        $pdo = pdoSqlConnect();
        $query = "insert into bookmark_relations_example (no, word_no, example, is_deleted) values (?,?,?,?)$question_marks;";

        $st = $pdo->prepare($query);
        foreach ($sentenList as $row => $value) {
            $st->bindValue($int + 1, $no);
            $st->bindValue($int + 2, $wordResult);
            $st->bindValue($int + 3, $value);
            $st->bindValue($int + 4, $is_deleted);
            $int = $int + 4;
        }
        $st->execute();

        $st = null;
        $pdo = null;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}

function move_word_is_exist_ex($newbookmarkNo, $bookmarkNo, $sentenList, $value, $master_wordNo)
{
    try {

        $is_deleted = (string)'N';
        $is_deleted2 = (string)'Y';
        $no = (int)0;
        $int = (int)0;

        $pdo = pdoSqlConnect();
        $query = "update bookmark_relations_word set is_deleted = ? where  bookmark_no = ? and no = ? and is_deleted = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$is_deleted2, $bookmarkNo, $value, $is_deleted]);
        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query = "select no from bookmark_relations_word where bookmark_no =? and is_deleted = ? and word_no = ?;";

        $st = $pdo->prepare($query);

        $st->execute([$newbookmarkNo, $is_deleted, $master_wordNo]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        $wordResult = $res[0]['no']; // insert 사용

        $count = count($sentenList);
        $question_marks = str_repeat(",(?,?,?,?)", $count - 1);

        $pdo = pdoSqlConnect();
        $query = "insert into bookmark_relations_example (no, word_no, example, is_deleted) values (?,?,?,?)$question_marks;";

        $st = $pdo->prepare($query);

        foreach ($sentenList as $row => $value) {
            $st->bindValue($int + 1, $no);
            $st->bindValue($int + 2, $wordResult);
            $st->bindValue($int + 3, $value);
            $st->bindValue($int + 4, $is_deleted);
            $int = $int + 4;
        }
        $st->execute();

        $st = null;
        $pdo = null;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}

function write_copy_word($newbookmarkNo, $master_wordNo)
{
    $no = (int)0;
//    $int = (int)0;
    $is_memorized = (string)'N';
    $is_registered = date("Y-m-d H:i:s");
    $is_deleted = 'N';
    $pdo = pdoSqlConnect();
    $query = "insert into bookmark_relations_word (no, is_memorized, word_no, is_deleted, bookmark_no, registered_at)
values (?, ?, ?, ?, ?, ?);";
//    echo "$query";
    $st = $pdo->prepare($query);
    $st->execute([$no, $is_memorized, $master_wordNo, $is_deleted, $newbookmarkNo, $is_registered]);

    $st = null;
    $pdo = null;
}

function write_copy_word_with_ex($newbookmarkNo, $master_wordNo, $sentenList)
{
    try {

        $no = (int)0;
        $int = (int)0;
        $is_memorized = (string)'N';
        $is_registered = date("Y-m-d H:i:s");
        $is_deleted = 'N';
        $pdo = pdoSqlConnect();
        $query = "insert into bookmark_relations_word (no, is_memorized, word_no, is_deleted, bookmark_no, registered_at)
values (?, ?, ?, ?, ?, ?);";

        $st = $pdo->prepare($query);
        $st->execute([$no, $is_memorized, $master_wordNo, $is_deleted, $newbookmarkNo, $is_registered]);

        $st = null;
        $pdo = null;

        $pdo = pdoSqlConnect();
        $query = "select no from bookmark_relations_word order by no desc limit 1;";

        $st = $pdo->prepare($query);
        //    $st->execute([$param,$param]);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st = null;
        $pdo = null;

        $word = $res[0]['no'];

        $count = count($sentenList);
        $question_marks = str_repeat(",(?,?,?,?)", $count - 1);
        $pdo = pdoSqlConnect();
        $query = "insert into bookmark_relations_example (no, word_no, example, is_deleted)
values (?, ?, ?, ?)$question_marks;";
//    echo "$query";

        $st = $pdo->prepare($query);
        foreach ($sentenList as $value)
        {
            $st->bindValue(1, $no);
            $st->bindValue(2, $word);
            $st->bindValue(3, $value);
            $st->bindValue(4, $is_deleted);
            $int = $int + 4;
        }
        $st->execute();

        $st = null;
        $pdo = null;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
        return 2;
    }
}

function update_move_word($value, $newbookmarkNo, $bookmarkNo)
{
    $is_registered = date("Y-m-d H:i:s");
    $id_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "UPDATE bookmark_relations_word
                        SET bookmark_no = ?, registered_at = ?
                        WHERE NO = ? and is_deleted = ? and bookmark_no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$newbookmarkNo, $is_registered, $value, $id_deleted, $bookmarkNo]);
    $st = null;
    $pdo = null;
}

function save_word_information($value, $bookmarkNo)
{
    $sentenList = array();
    $count = 0;
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select bookmark_relations_word.word_no as master_wordNo, example as sentence, bookmark_relations_example.no
from bookmark_relations_word
         left outer join bookmark_relations_example on bookmark_relations_word.no = bookmark_relations_example.word_no
where bookmark_relations_word.no = ?
  and bookmark_relations_word.is_deleted = ?
and bookmark_relations_word.bookmark_no = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$value, $is_deleted, $bookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $no = $res[0]['no'];

    if (strlen($no) < 1)
    {
        $no = 0;
    } else
    {
        $no = 1;
    }

    foreach ($res as $row) {
        $sentenList[$count++] = $row['sentence'];
    }

    $st = null;
    $pdo = null;

    return array("wordNo" => intval($res[0]["master_wordNo"]), "sentence" => $sentenList, "exNo" => $no);
}

function wordcheck2($value, $bookmarkNo)
{
    $is_deleted = 'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark_relations_word where no = ? and is_deleted = ? and bookmark_no = ?)as exist;";

    $st = $pdo->prepare($query);
    $st->bindValue(1, $value);
    $st->bindValue(2, $is_deleted);
    $st->bindValue(3, $bookmarkNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function copyExample($newbookmarkNo, $sentenList, $master_wordNo)
{
    $is_deleted = (string)'N';
    $int = 0;
    $no = 0;
    $pdo = pdoSqlConnect();
    $query = "select no from bookmark_relations_word where is_deleted = ? and word_no = ? and bookmark_no = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $master_wordNo, $newbookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    $wordNo = $res[0]['no'];
    $count = count($sentenList);
    $question_marks = str_repeat(",(?,?,?,?)", $count - 1);

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO bookmark_relations_example (no, word_no, example, is_deleted) VALUES (?,?,?,?)$question_marks;";

    $st = $pdo->prepare($query);
    foreach ($sentenList as $row => $value) {
        $st->bindValue($int + 1, $no);
        $st->bindValue($int + 2, $wordNo);
        $st->bindValue($int + 3, $value);
        $st->bindValue($int + 4, $is_deleted);
        $int = $int + 4;
    }
    $st->execute();

    $st = null;
    $pdo = null;
}

function copy_add_Example($newbookmarkNo, $sentenList, $master_wordNo)
{
    $int = 0;
    $no = 0;
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select no from bookmark_relations_word where word_no = ? and bookmark_no = ? and is_deleted = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$master_wordNo, $newbookmarkNo, $is_deleted]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    $wordNo = $res[0]['no'];

    $count = count($sentenList);
    $question_marks = str_repeat(",(?,?,?,?)", $count - 1);

    $pdo = pdoSqlConnect();
    $query = "INSERT INTO bookmark_relations_example (no, word_no, example, is_deleted) VALUES (?,?,?,?)$question_marks;";

    $st = $pdo->prepare($query);
    foreach ($sentenList as $value) {
        $st->bindValue($int + 1, $no);
        $st->bindValue($int + 2, $wordNo);
        $st->bindValue($int + 3, $value);
        $st->bindValue($int + 4, $is_deleted);
        $int = $int + 4;
    }
    $st->execute();

    $st = null;
    $pdo = null;
}

////READ
//function test()
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM TEST_TB;";
//
//    $st = $pdo->prepare($query);
//    //    $st->execute([$param,$param]);
//    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
////READ
//function testDetail($testNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM TEST_TB WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$testNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//
//function testPost($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO TEST_TB (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}

// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }

function bookmark_check($newbookmarkNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark where is_deleted = ? and no = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $newbookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function is_already_ex($value)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark_relations_example where is_deleted = ? and word_no = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $value]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function is_already_word($master_wordNo, $newbookmarkNo)
{
//    echo "wordmaster : $master_wordNo";
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark_relations_word where is_deleted = ? and word_no = ? and bookmark_no = ?) as exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $master_wordNo, $newbookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function ex_exist($bookmarkNo, $sentenseNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select *
              from bookmark_relations_example
                       inner join bookmark_relations_word
                                  on bookmark_relations_example.word_no = bookmark_relations_word.no
              where bookmark_relations_example.is_deleted = ?
                and bookmark_relations_example.no = ?
                and bookmark_no = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $sentenseNo, $bookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function word_exist($bookmarkNo, $wordNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark_relations_word  where bookmark_no = ? and is_deleted = ? and no = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$bookmarkNo, $is_deleted, $wordNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function bookmark_exist($userNo, $bookmarkNo)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from bookmark  where user_no = ? and is_deleted = ? and no = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userNo, $is_deleted, $bookmarkNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function select_master_word($word)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select no from word_master where word = ? and is_deleted = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$word, $is_deleted]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['no'];
}

function master_word($word)
{
    $no = (int)0;
    $word = (string)$word;
    $is_deleted = (string)'N';
    $is_registered = date("Y-m-d H:i:s");

    $pdo = pdoSqlConnect();
    $query = "insert into word_master (no, word, is_deleted, registered_at) values (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$no, $word, $is_deleted, $is_registered]);

    $st = null;
    $pdo = null;

    $pdo = pdoSqlConnect();
    $query = "select no from word_master where  is_deleted = ? order by registered_at desc;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['no'];
}

function is_exist_word($word)
{
    $is_deleted = (string)'N';
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from word_master where is_deleted = ? and word = ?) as exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$is_deleted, $word]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

function convert_to_userNo($androidNum)
{
    $pdo = pdoSqlConnect();
    $query = "select no from user where android_id = ?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$androidNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['no'];
}

function isValidJWToken($androidNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE android_id= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$androidNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return array("intval" => intval($res[0]["exist"]), "android_id" => $androidNum);
}
