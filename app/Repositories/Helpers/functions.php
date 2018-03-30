<?php

use Illuminate\Support\Facades\Auth;

/**
 * Override hàm lấy đường dẫn
 * @param  string $path
 * @return string
 */
function get_asset($path)
{
    return asset($path, env('APP_ENV') == 'production');
    // return asset($path);
}

function getAphabetByIndex($index)
{
    $index -= 1;
    $aphabets = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    return isset($aphabets[$index]) ? $aphabets[$index] : 'A';
}

function tolowerCase($str)
{
    $arrLower = [
        "à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ",
        "è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ",
        "ì","í","ị","ỉ","ĩ",
        "ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ",
        "ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
        "ỳ","ý","ỵ","ỷ","ỹ",
        "đ",
    ];

    $arrUpper = [
        "À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ",
        "È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
        "Ì","Í","Ị","Ỉ","Ĩ",
        "Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ",
        "Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
        "Ỳ","Ý","Ỵ","Ỷ","Ỹ",
        "Đ",
    ];

    return str_replace($arrUpper, $arrLower, $str);
}

function vn_str_filter($str)
{
    // $str = mb_convert_encoding($str, "UTF-8", "auto");
    // $str = iconv(mb_detect_encoding($str, mb_detect_order(), true), "UTF-8", $str);
    // dd($str);

    $unicode = [
        'a' =>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
        'b' => 'b',
        'c' => 'c',
        'd' =>'đ',
        'e' =>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'f' => 'f',
        'g' => 'g',
        'h' => 'h',
        'i' =>'í|ì|ỉ|ĩ|ị',
        'j' => 'j',
        'k' => 'k',
        'l' => 'l',
        'm' => 'm',
        'n' => 'n',
        'o' =>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'p' => 'p',
        'q' => 'q',
        'r' => 'r',
        's' => 's',
        't' => 't',
        'u' =>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'v' => 'v',
        'w' => 'w',
        'x' => 'x',
        'y' =>'ý|ỳ|ỷ|ỹ|ỵ',
        'z' => 'z',
        'A' =>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'B' => 'B',
        'C' => 'C',
        'D' =>'Đ',
        'E' =>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'F' => 'F',
        'G' => 'G',
        'H' => 'H',
        'I' =>'Í|Ì|Ỉ|Ĩ|Ị',
        'J' => 'J',
        'K' => 'K',
        'L' => 'L',
        'M' => 'M',
        'N' => 'N',
        'O' =>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'P' => 'P',
        'Q' => 'Q',
        'R' => 'R',
        'S' => 'S',
        'T' => 'T',
        'U' =>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'V' => 'V',
        'W' => 'W',
        'X' => 'X',
        'Y' =>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        'Z' => 'Z',
        ' ' => ' ',
    ];

    foreach ($unicode as $nonUnicode=>$uni) {
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }

    $unicode['.'] = '.';
    $unicode['-'] = '-';

    $arr = str_split(mb_strtolower($str));
    foreach ($arr as $key => $value) {
        if (!array_key_exists($value, $unicode) && !is_numeric($value)) {
            unset($arr[$key]);
        }
    }

    $str = implode('', $arr);

    return $str;
}

function str_filter_vn($str)
{
    $str = mb_convert_encoding($str, "UTF-8", "auto");
    $str = iconv(mb_detect_encoding($str, mb_detect_order(), true), "UTF-8", $str);
    // dd($str);

    // $unicode = array(
    //     'a' =>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
    //     'b' => 'b',
    //     'c' => 'c',
    //     'd' =>'đ',
    //     'e' =>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
    //     'f' => 'f',
    //     'g' => 'g',
    //     'h' => 'h',
    //     'i' =>'í|ì|ỉ|ĩ|ị',
    //     'j' => 'j',
    //     'k' => 'k',
    //     'l' => 'l',
    //     'm' => 'm',
    //     'n' => 'n',
    //     'o' =>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
    //     'p' => 'p',
    //     'q' => 'q',
    //     'r' => 'r',
    //     's' => 's',
    //     't' => 't',
    //     'u' =>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
    //     'v' => 'v',
    //     'w' => 'w',
    //     'x' => 'x',
    //     'y' =>'ý|ỳ|ỷ|ỹ|ỵ',
    //     'z' => 'z',
    //     'A' =>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
    //     'B' => 'B',
    //     'C' => 'C',
    //     'D' =>'Đ',
    //     'E' =>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
    //     'F' => 'F',
    //     'G' => 'G',
    //     'H' => 'H',
    //     'I' =>'Í|Ì|Ỉ|Ĩ|Ị',
    //     'J' => 'J',
    //     'K' => 'K',
    //     'L' => 'L',
    //     'M' => 'M',
    //     'N' => 'N',
    //     'O' =>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
    //     'P' => 'P',
    //     'Q' => 'Q',
    //     'R' => 'R',
    //     'S' => 'S',
    //     'T' => 'T',
    //     'U' =>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
    //     'V' => 'V',
    //     'W' => 'W',
    //     'X' => 'X',
    //     'Y' =>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
    //     'Z' => 'Z',
    //     ' ' => ' ',
    //     '  ' => ' '
    // );

    // foreach($unicode as $nonUnicode=>$uni){
    //     $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    // }

    // $arr = str_split(mb_strtolower($str));
    // foreach ($arr as $key => $value) {
    //     if (!array_key_exists($value, $unicode) && !is_numeric($value)) {
    //         unset($arr[$key]);
    //     }
    // }

    // $str = implode('', $arr);

    return $str;
}

function removeTitle($str, $keyReplace = ' ')
{
    $str = vn_str_filter($str);
    return str_replace($keyReplace, "-", $str);
}

function formatToTextSimple($str)
{
    $str = vn_str_filter($str);
    return str_replace(" ", "_", $str);
}

function getCurrentUser($guard = '')
{
    if (Auth::check()) {
        if ($guard != '') {
            return Auth::guard($guard)->user();
        }
        return Auth::user();
    }
    return false;
}

function convert_uuid2id($uuid) {
    if ($decodeId = \Hashids::decode($uuid)) {
        return $decodeId[0];
    }
    return $uuid;
}

function list_sex() {
    return [
        SEX_UNKNOWN => 'Không xác định',
        SEX_MALE    => 'Nam',
        SEX_FEMALE  => 'Nữ'
    ];
}

function list_level() {
    return [
        LEVEL_NORMAL  => 'Thường',
        LEVEL_SILVER  => 'Bạc',
        LEVEL_GOLD    => 'Vàng',
        LEVEL_DIAMOND => 'Kim cương'
    ];
}

function list_level_point() {
    return [
        LEVEL_NORMAL  => 0,
        LEVEL_SILVER  => 10000,
        LEVEL_GOLD    => 25000,
        LEVEL_DIAMOND => 50000
    ];
}

/**
 * [generateWebhookToken description]
 * @param  [type] $data          [description]
 * @param  [type] $client_secret [description]
 * @return [type]                [description]
 */
function generateWebhookToken($data, $client_secret)
{
    $data = json_encode($data);
    return base64_encode(hash_hmac('sha256', $data, $client_secret, true));
}