<?php

// namespace App\Helpers;
/*
|--------------------------------------------------------------------------
| My Custom Helper Functions
|--------------------------------------------------------------------------
|
| Feel free to use anytime :)
|
*/

use Carbon\Carbon;
use App\Models\Gig;
use App\Models\Tag;
use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\UserRate;
use App\Models\Application;
use App\Models\OrderWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\BillingInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

if (!function_exists('api_request_response')) {
    /**
     * Use this method to return API Responses
     *
     * @param $status
     * @param $message
     * @param $statusCode
     * @param array $data
     * @param bool $return
     * @param bool $returnArray
     * @return bool|\Illuminate\Http\JsonResponse
     */
    function api_request_response($status, $message, $statusCode, $data = [], $return = false, $returnArray = false)
    {
        $responseArray = [
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        $response = response()->json(
            $responseArray
        );

        if ($returnArray) {
            return $returnArray;
        }

        if ($return) {
            return $response;
        }

        header('Content-Type: application/json', true, $statusCode);

        echo json_encode($response->getOriginalContent());

        exit();
    }
}

if (!function_exists('success_status_code')) {
    /**
     * Returns api success code
     *
     * @return int
     */
    function success_status_code()
    {
        return 200;
    }
}

if (!function_exists('unauthorized_status_code')) {
    function unauthorized_status_code()
    {
        return 401;
    }
}

if (!function_exists('bad_response_status_code')) {
    /**
     * Return bad response code, 400
     *
     * @return int
     */
    function bad_response_status_code()
    {
        return 400;
    }
}

/**
 * Generete UUID
 *
 * Requires Ramsey/uuid
 * TODO: Installation => composer require ramsey/uuid
 *
 * @return string
 * @throws \Exception
 */

if (!function_exists('generate_uuid')) {
    function generate_uuid()
    {
        return \Ramsey\Uuid\Uuid::uuid1()->toString();
    }
}

if (!function_exists('generate_access_token')) {
    /**
     * Generate random password for user based on app name
     *
     * @param User $user
     * @return mixed
     */
    function generate_access_token(User $user)
    {
        return $user
            ->createToken(config("app.name"))
            ->accessToken;
    }
}

if (!function_exists('generate_random_password')) {
    /**
     * @param int $length
     * @return string
     */
    function generate_random_password($length = 8)
    {
        /** @noinspection PhpDeprecationInspection */
        return str_random($length);
    }
}

if (!function_exists('get_account_period')) {
    /**
     * @param $startDate
     * @param $endDate
     * @return \DatePeriod
     * @throws \Exception
     */
    function get_account_period($startDate, $endDate)
    {
        $start    = (new \DateTime($startDate))->modify('first day of this month');
        $end      = (new \DateTime($endDate))->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);
        return $period;
    }
}

if (!function_exists('uploadFile')) {
    /**
     * Use method to upload files to storage
     *
     * @param $request
     * @param $newName
     * @param $fileName
     * @return mixed
     */
    function uploadFile($request, $newName, $fileName)
    {
        $file = $request->file($fileName);

        $fileName = $newName . "." . $file->getClientOriginalExtension();

        $fileStore = $file->storeAs('public/', $fileName);

        return Storage::url("$fileStore");
    }
}

/**
 * Requires Intervention image
 * TODO: Installation => composer require intervention/image
 * TODO: make sure to add 'Image' => Intervention\Image\ImageManagerStatic::class, instead of 'Image' => Intervention\Image\Facades\Image::class, Issues occured while using it
 *
 * Memory Limit is bumped because Intervention Image can go beyond allowed memory.
 * e.g Allowed memory size of 134217728 bytes exhausted (tried to allocate 24576 bytes) in /Users/olaoluwani/Sites/laravel/tfolc/vendor/intervention/image/src/Intervention/Image/Gd/Decoder.php on line 136
 *
 */
if (!function_exists('cropAndUploadFile')) {
    function cropAndUploadFile($request, $newName, $fileName, $width, $height)
    {
        ini_set('memory_limit', '256M');

        $imageFile = $request->file($fileName);

        $img = Image::make($imageFile);

        $newImage = $img->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        $newImageFilePath = "public/$width" . "x$height/" . $newName . "." . $imageFile->getClientOriginalExtension();

        Storage::put($newImageFilePath, (string) $newImage->encode());

        return Storage::url("$newImageFilePath");
    }
}

if (!function_exists('format_date')) {
    /**
     * Use this method to format date
     *
     * @param $date
     * @param $newFormat
     * @return mixed
     */
    function format_date($date, $newFormat)
    {
        return Carbon::parse($date)->format($newFormat);
    }
}

if (!function_exists('delete_object')) {
    /**
     * Delete an object
     *
     * @param $uuid
     * @param $model
     * @param $name
     * @param null $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    function delete_object($uuid, $model, $name, $redirect = null)
    {
        try {
            $object = $model::where("uuid", $uuid)->firstOrFail();
            $object->delete();

            $response = "$name deleted successfully!";

            if ($redirect != null) {
                return redirect()->route("$redirect")->with('success', "$response");
            } else {
                return redirect()->back()->with('success', "$response");
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('exception', $exception->getMessage());
        }
    }
}

if (!function_exists('store_or_update_object')) {

    /**
     * Use this method to store or update data
     *
     * @param $request
     * @param $model
     * @param $data
     * @param $uuid
     * @param $objectName
     * @param null $redirect
     * @param null|array|string $fileName
     * @param null $fileNewName
     * @param array $imageResizeSize
     * @param bool $toUpdate
     * @param bool $noRedirect
     * @return array|\Illuminate\Http\RedirectResponse|string
     */
    function store_or_update_object(
        $request,
        $model,
        $data,
        $uuid,
        $objectName,
        $redirect = null,
        $fileName = null,
        $fileNewName = null,
        $imageResizeSize = [],
        $toUpdate = false,
        $noRedirect = false
    ) {
        try {
            $dbData = [];

            if ($fileNewName != null) {
                if (is_array($fileName)) {
                    foreach ($fileName as $filename) {
                        uploadFile(
                            $request,
                            $fileNewName,
                            $filename
                        );
                    }
                } else {
                    uploadFile(
                        $request,
                        $fileNewName,
                        $fileName
                    );
                }

                if (!empty($imageResizeSize)) {
                    if (is_array($fileName)) {
                        foreach ($fileName as $filename) {
                            /*
                             * Task: Confirm if the specified file is an image before cropping
                             */
                            $isImage = $request->file($filename)->getClientOriginalExtension();

                            $imageExtensions = ["jpg", "jpeg", "png"];

                            if (in_array($isImage, $imageExtensions)) {
                                foreach ($imageResizeSize as $size) {
                                    cropAndUploadFile(
                                        $request,
                                        $fileNewName,
                                        $filename,
                                        $size['width'],
                                        $size['height']
                                    );
                                }
                            }
                        }
                    } else {
                        foreach ($imageResizeSize as $size) {
                            cropAndUploadFile(
                                $request,
                                $fileNewName,
                                $fileName,
                                $size['width'],
                                $size['height']
                            );
                        }
                    }
                }
            }

            if (!$toUpdate) {
                $dbData = $model::create($data);

                $response = "$objectName stored successfully!";
            } else {
                $dbData = $model::where('uuid', $uuid)->update($data);

                $response = "$objectName updated successfully!";
            }

            if (!$noRedirect) {
                if ($redirect != null) {
                    return redirect()->route("$redirect")->with('success', "$response");
                } else {
                    return redirect()->back()->with('success', "$response");
                }
            } else {
                return $dbData;
            }
        } catch (\Exception $exception) {
            if (!$noRedirect) {
                return redirect()->back()->withErrors(["exception" => $exception->getMessage()]);
            } else {
                return $exception->getMessage();
            }
        }
    }
}

if (!function_exists('update_object')) {
    function update_object(
        $request,
        $model,
        $data,
        $uuid,
        $objectName,
        $redirect = null,
        $fileName = null,
        $fileNewName = null,
        $imageResizeSize = []
    ) {
        try {
            if ($fileNewName != null) {

                $filePath = uploadFile(
                    $request,
                    $fileNewName,
                    $fileName
                );

                if (!empty($imageResizeSize)) {
                    $filePath = cropAndUploadFile(
                        $request,
                        $fileNewName,
                        $fileName,
                        $imageResizeSize['width'],
                        $imageResizeSize['height']
                    );
                }
            }

            $data = $model::where('uuid', $uuid)->update($data);

            $response = "$objectName updated successfully!";

            if ($redirect != null) {
                return redirect()->route("$redirect")->with('success', "$response");
            } else {
                return redirect()->back()->with('success', "$response");
            }
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(["exception" => $exception->getMessage()]);
        }
    }

    if (!function_exists("generate_slug_with_uuid_suffix")) {
        /**
         * Use this method to generate slug with the uuid as suffix
         *
         * @param $subject
         * @param $uuid
         * @return string
         */
        function generate_slug_with_uuid_suffix($subject, $uuid)
        {
            return Str::slug($subject) . "-" . str_replace(["-", "-"], "", $uuid);
        }
    }
}


function API_Response($statusCode, $response = false, $errorBag = false)
{
    $status = ($statusCode == 200) ? "success" : (($statusCode == 500) ?  "error" : ($statusCode == 404 ? "warning" : ($statusCode == 100 ? "info" : "unknown")));
    $responseAPI = [
        "status" => $status,
        "statusCode" => $statusCode,
    ];
    if ($errorBag) $responseAPI["errors"] = $errorBag;
    if ($response) $responseAPI["responseBody"] = $response;
    return $responseAPI;
}

function generateNumCodeLength($num)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $num; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAuthUser($value)
{
    return auth()->user()->$value;
}

function getUserCountry()
{
    $ip = request()->ip();

    $apiKey = '525176d80bfbe2bb8f9979c76ce3d77b0a5c6e1a31d39075b8288660';
    $client = new Client();
    try {
        $response = $client->get("https://api.ipdata.co/{$ip}?api-key={$apiKey}");

        $data = json_decode($response->getBody(), true);

        $country = $data['country_name'] ?? 'Unknown';
        return response()->json(['country' => $country]);
    } catch (\Throwable $th) {
        return response()->json(['country' => 'Unknown']);
    }
}

function formDateFormat($date, $format)
{
    return Carbon::parse($date)->format($format);
}

function getSettings($title)
{
    $value = Setting::where("title", $title)->first();
    if ($value) {
        return $value->value;
    } else {
        return;
    }
}


function formatForURL($str)
{
    $formattedStr = strtolower(preg_replace('/[^a-zA-Z0-9]+/', ' ', $str));
    $formattedStr = str_replace(' ', '-', $formattedStr);
    return $formattedStr;
}
function unformatFromURL($formattedStr)
{
    $unformattedStr = str_replace('-', ' ', $formattedStr);
    $unformattedStr = ucwords($unformattedStr);
    $unformattedStr = trim($unformattedStr);

    return $unformattedStr;
}

function currency()
{
    return "$";
}

function currencyName()
{
    return "USD";
}

function amount($number)
{
    return currency() . number_format($number, 2);
}


function chop_string($input_string, $length, $to = false)
{
    $length_limit = $length;
    $chopped_string = substr($input_string, 0, $length_limit);

    if (strlen($input_string) > $length_limit) {
        $chopped_string .= '...';
    }

    if ($to) {
        return $chopped_string . substr($input_string, strlen($input_string) - $to, strlen($input_string));
    }
    return $chopped_string;
}

function getUserById($id)
{
    return User::find($id);
}

function getUserRate($user_id)
{
    return UserRate::where("user_id", $user_id)->first();
}

function getAuthBillingInfo($value)
{
    $billing_info = BillingInformation::where("user_id", getAuthUser("id"))->first();
    return @$billing_info->$value;
}

function htmlForOrderStatus($order_status)
{
    if ($order_status == "incomplete") {
        echo "<div class='badge badge-warning p-2'>INCOMPLETE</div>";
    } elseif ($order_status == "processing") {
        echo "<div class='badge badge-info p-2'>PROCESSING</div>";
    } else {
        echo "<div class='badge badge-danger p-2'>EXPIRED</div>";
    }
}

function getGigBy($key, $value)
{
    return Gig::where($key, $value)->first();
}


function getOrderTransactions($transaction_id)
{
    return Transaction::find($transaction_id);
}

function getOrderWallet($transaction_id)
{
    return OrderWallet::where("transaction_id", $transaction_id)->first();
}

function getGigPackage($gig, $package, $for)
{
    $packageKey = "package_" . $package . "_$for";
    return $gig->$packageKey;
}

function getExpiredDate($startDate, $EndDate)
{
    return strtotime($startDate . "+" . $EndDate);
}

function formatFileSize($kilo_bytes)
{
    $units = array('KB', 'MB', 'GB', 'TB');
    $index = 0;

    while ($kilo_bytes >= 1000 && $index < count($units) - 1) {
        $kilo_bytes /= 1000;
        $index++;
    }

    return round($kilo_bytes, 2) . ' ' . $units[$index];
}


function getNumberOfDaysInDatetimeNow($datetime)
{

    $date1 = new DateTime(now());
    $date2 = new DateTime($datetime);
    $interval = $date1->diff($date2);
    return $interval->days;
}

function changeRateToPercentage($rate)
{
    return floor(($rate / 5) * 100);
}

function calcRateAverages($items, $key = null, $default = 0)
{
    $ans = 0;
    if (count($items) > 0) {
        $total = 0;
        $count = 0;
        foreach ($items as $p) {
            if ($key) {
                $total += $p->$key;
            } else {
                $total += $p;
            }
            $count++;
        }
        $ans = $total / $count;
    } else {
        $ans = $default;
    }
    return round($ans, 1);
}

function runSQL($sql)
{
    return DB::select($sql);
}

function getUserRateStar($userID)
{
    $userRate = \App\Models\UserRate::where('user_id', $userID)->get();
    return getRateStar($userRate);
}

function getRateStar($gigRate, $forUser = true)
{
    $communicationRate = calcRateAverages($gigRate, "communication_rate");
    $serviceRate = calcRateAverages($gigRate, "service_rate");
    $recommendationRate = calcRateAverages($gigRate, "recommended_rate");
    $responseRate = $forUser ? calcRateAverages($gigRate, "response_rate") : 0;

    $total = $forUser ? 4 : 3;
    $totalRate = $communicationRate + $serviceRate + $recommendationRate + $responseRate;

    return round($totalRate / $total, 1);
}

function calculateRateGroup($userRateAll)
{
    $rates = [];
    foreach ($userRateAll as $userRate) {
        $calcRate = ($userRate->communication_rate + $userRate->service_rate + $userRate->recommended_rate);
        $calcRate = round($calcRate / 3, 1);
        if ($calcRate < 2) {
            $rates["1"][] = $calcRate;
        } else if ($calcRate < 3) {
            $rates["2"][] = $calcRate;
        } else if ($calcRate < 4) {
            $rates["3"][] = $calcRate;
        } else if ($calcRate < 4.5) {
            $rates["4"][] = $calcRate;
        } else {
            $rates["5"][] = $calcRate;
        }
    }

    return $rates;
}


function orderArrayDescList($array)
{
    $new_Array = [];
    $array_keys = array_keys($array);
    rsort($array_keys);
    foreach ($array_keys as $key) {
        $new_Array[$key] = $array[$key];
    }
    return $new_Array;
}


function getUserImage($image)
{
    if (!empty($image) && file_exists(public_path('images/profiles/' . $image))) {
        return  $image;
    } else {
        return "avatar.png";
    }
}

function everyArray($array, $callback)
{
    foreach ($array as $k => $ar) {
        $response = $callback($ar, $k);
        if (!$response) return false;
    }
    return true;
}


function sum($array, $callback = false)
{
    $sum = 0;
    foreach ($array as $ar) {
        $sum += $callback ? $callback($ar) : $ar;
    }
    return $sum;
}

function getGigRate($gig_id, $user_id)
{
    return  UserRate::leftJoin('orders', 'user_rates.gig_order_id', 'orders.order_id')
        ->where('orders.gig_reg_id', $gig_id)
        ->where('user_rates.user_id', $user_id)
        ->selectRaw('user_rates.*,orders.*,user_rates.id as rate_id')
        ->orderBy('user_rates.id', 'DESC')
        ->get();
}
function getUserGigsRate($user_id)
{
    return  UserRate::leftJoin('orders', 'user_rates.gig_order_id', 'orders.order_id')
        ->where('user_rates.user_id', $user_id)
        ->selectRaw('user_rates.*,orders.*,user_rates.id as rate_id')
        ->orderBy('user_rates.id', 'DESC')
        ->get();
}


function getPopularGigs($gigs)
{
    $res_gigs = [];
    foreach ($gigs as $gig_k => $gigs_value) {
        if ($gigs_value->orders_count >= 2) {
            $res_gigs[] = $gigs_value;
        }
    }

    return $res_gigs;
}

function array_some($array, $callback)
{
    if (count($array) > 0) {
        foreach ($array as $a) {
            if ($callback($a)) {
                return true;
            }
        }
    } else {
        return false;
    }
}

function getTagsForGigs($gigs)
{
    $tags = array();
    foreach ($gigs as $g) {
        $ts = json_decode($g->tags, true);
        foreach ($ts as $t) {
            $tag = Tag::where("name", trim($t))->first();
            if ($tag) {
                $in_array = array_some($tags, function ($rt) use ($tag) {
                    return $rt->id == $tag->id;
                });
                if (!$in_array) {
                    $tags[] = $tag;
                }
            }
        }
    }
    return $tags;
}

function gigs_with_tags($gigs, $tag)
{
    $new_gigs = array();
    foreach ($gigs as $gig) {
        $gig_tag = json_decode($gig->tags);
        if (in_array($tag, $gig_tag)) {
            $new_gigs[] = $gig;
        }
    }
    return $new_gigs;
}

function getTopSeller($sub_category)
{
    $users = User::where("users.status", "active")
        ->leftJoin("gigs", "gigs.user_id", "users.id")
        ->where("gigs.sub_category", $sub_category)
        ->where("users.acct_level", 100)
        ->where("type", "freelancer")
        ->orderBy("users.id", "DESC")
        ->selectRaw("users.id as user_id")
        ->get();
    $top_sellers = array();
    foreach ($users as $user) {
        $userRate = getUserRateStar($user->user_id);
        if ($userRate >= 4) {
            $top_sellers[] = $user;
        }
    }
    return $top_sellers;
}

function getNewSellers($sub_category)
{
    $users = User::where("users.status", "active")
        ->leftJoin("gigs", "gigs.user_id", "users.id")
        ->where("gigs.sub_category", $sub_category)
        ->where("users.acct_level", 100)
        ->where("type", "freelancer")
        ->orderBy("users.id", "DESC")
        ->selectRaw("users.id as user_id,users.created_at as user_created_at")
        ->get();
    $new_Users = [];
    foreach ($users as $user) {

        if (userAsNewExpiredDate($user->user_created_at) > strtotime(now())) {
            $new_Users[] = $user;
        }
    }
    return $new_Users;
}

function gigDropdownFilter($gigs, $filterOptions)
{
    $filter_gigs = array();
    foreach ($gigs as $gig) {
        if (isset($filterOptions["service_options"])) {
            $tags = json_decode($gig["tags"], true);
            $service_options = array_map(function ($key) {
                $tag = Tag::where("format_url", $key)->first();
                return $tag->name;
            }, $filterOptions["service_options"]);
            if (is_value_in_array($tags, $service_options)) {
                $filter_right = true;
            } else {
                $filter_right = false;
            }
        }
        if (isset($filterOptions["seller_details"])) {
            foreach ($filterOptions["seller_details"] as $details) {
                if ($details == "top_seller") {
                    $userRate = getUserRateStar($gig->user_id);
                    if ($userRate >= 4) {
                        $filter_right = true;
                    } else {
                        $filter_right = false;
                    }
                }
                if ($details == "new_seller") {
                    $user = User::find($gig->user_id);
                    if (userAsNewExpiredDate($user->created_at) > strtotime(now())) {
                        $filter_right = true;
                    } else {
                        $filter_right = false;
                    }
                }
            }
        }
        if (isset($filterOptions["budget"])) {
            $min = $filterOptions["budget"]["min"];
            $max = $filterOptions["budget"]["max"];
            if (is_numeric($min) && is_numeric($max)) {

                if (
                    ($min <= $gig->package_basic_price || $min <= $gig->package_standard_price || $min <= $gig->package_premium_price)
                    && ($max >= $gig->package_basic_price || $max >= $gig->package_standard_price || $max >= $gig->package_premium_price)
                ) {
                    $filter_right = true;
                } else {
                    $filter_right = false;
                }
            } else {
                if (is_numeric($min)) {
                    if ($min <= $gig->package_basic_price || $min <= $gig->package_standard_price || $min <= $gig->package_premium_price) {
                        $filter_right = true;
                    } else {
                        $filter_right = false;
                    }
                }
                if (is_numeric($max)) {
                    if ($max >= $gig->package_basic_price || $max >= $gig->package_standard_price || $max >= $gig->package_premium_price) {
                        $filter_right = true;
                    } else {
                        $filter_right = false;
                    }
                }
            }
        }
        if (isset($filterOptions["delivery_time"])) {
            if ($filterOptions["delivery_time"] == "anytime") {
                $filter_right = true;
            } else if (($filterOptions["delivery_time"] >= $gig->package_basic_day_delivery) || ($filterOptions["delivery_time"] >= $gig->package_standard_day_delivery) || ($filterOptions["delivery_time"] >= $gig->package_premium_day_delivery)) {
                $filter_right = true;
            } else {
                $filter_right = false;
            }
        }
        if (isset($filterOptions["pro_service"]) && $filterOptions["pro_service"] == 1) {
            $gigUserRate = getGigRate($gig->reg_id, $gig->user_id);
            if (count($gigUserRate) > 0) {
                $gigRate = getRateStar($gigUserRate, false);
                if ($gigRate >= 4) {
                    $filter_right = true;
                } else {
                    $filter_right = false;
                }
            }
        }

        if (isset($filterOptions["online_seller"]) && $filterOptions["online_seller"] == 1) {
            $user = User::find($gig->user_id);
            if ($user->online_status == "online") {
                $filter_right = true;
            } else {
                $filter_right = false;
            }
        }

        if (@$filter_right) {
            $filter_gigs[] = $gig;
        }
    }

    return $filter_gigs;
}

function userAsNewExpiredDate($date)
{
    return strtotime($date . ' + 30days');
}


function is_value_in_array(array $array1, array $array2)
{
    if (empty($array1)) {
        return false;
    }
    foreach ($array1 as $value) {
        if (in_array($value, $array2)) {
            return true;
        }
    }
    return false;
}

function getUserRatePercentage($gig_reg_id, $for)
{
    $user_rates = UserRate::where("orders.gig_reg_id", $gig_reg_id)
        ->leftJoin("orders", "user_rates.gig_order_id", "orders.order_id")
        ->get();
    $total = 0;
    $total_finding = 0;
    foreach ($user_rates as $user_rate) {
        $total += 5;
        $total_finding += $user_rate->$for;
    }

    if ($total == 0) {
        return 0;
    } else {
        return floor(($total_finding / ($total)) * 100);
    }
}

function search_for_gig($query, $sortBy = "newest_arrivals")
{
    $gigs = [];
    if ($sortBy == "best_selling") {
        $get_gigs =   Gig::orderBy("id", "DESC")->get();
        foreach ($get_gigs as $g_g) {
            if (getUserRateStar($g_g->user_id) >= 4) {
                $gigs[] = $g_g;
            }
        }
    } else if ($sortBy == "recommended") {
        $get_gigs = Gig::orderBy("id", "DESC")->get();
        foreach ($get_gigs as $g_g) {
            if (getUserRatePercentage($g_g->reg_id, "recommended_rate") >= 50) {
                $gigs[] = $g_g;
            }
        }
    } else {
        $gigs = Gig::orderBy("id", "DESC")->get();
    }

    $search_gigs = array();
    foreach ($gigs as $g) {
        $tags = json_decode($g->tags, true);
        $search_t = array_some($tags, function ($t) use ($query) {
            return (strtolower($t) == strtolower($query)) || (preg_match("%$query%", $t));
        });
        $search_g = Gig::where("id", $g->id)->where(function ($sq) use ($query) {
            $sq->where("title", "LIKE", "%$query%")->orWhere("description", "LIKE", "%$query%");
        })->first();

        if ($search_g) {
            $search_gigs[] = $g;
        } else if ($search_t) {
            $search_gigs[] = $g;
        }
    }
    return $search_gigs;
}

function getGigsContainingTag($tag_)
{
    $filterTags = array();
    $gigs = Gig::orderBy("id", "DESC")->get();
    foreach ($gigs as $g) {
        $tags = json_decode($g->tags, true);
        $tag_exist = array_some($tags, function ($t) use ($tag_) {
            $find_tag = Tag::where("format_url", $tag_)->first();
            $check_tag = Tag::where("name", $t)->first();
            return @$find_tag->format_url == @$check_tag->format_url;
        });
        if ($tag_exist) {
            $filterTags[] = $g;
        }
    }
    return $filterTags;
}

function getAuthUserWallet($value)
{
    return @Wallet::where('user_id', auth()->user()->id)->first()->$value;
}

function getTotalJobApplicant($job_id)
{
    return Application::where("job_id", $job_id)->count();
}

function calculateTimeAgo($created_at)
{
    $createdDateTime = new DateTime($created_at);
    $currentDateTime = new DateTime();
    $interval = $currentDateTime->diff($createdDateTime);
    if ($interval->y > 0) {
        return $interval->y . " year" . ($interval->y > 1 ? "s" : "") . " ago";
    } elseif ($interval->d > 0) {
        return $interval->d . " day" . ($interval->d > 1 ? "s" : "") . " ago";
    } elseif ($interval->h > 0) {
        return $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
    } elseif ($interval->i > 0) {
        return $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
    } else {
        return $interval->s . " second" . ($interval->s > 1 ? "s" : "") . " ago";
    }
}


function onlineStatus()
{
    $users = User::all();
    foreach ($users as $user) {
        if (@auth()->user()->id == $user->id) {
            $user->online_status = "online";
            $user->offline_at  = date('Y-m-d H:i:s', strtotime("now + 3 minutes"));
        } else if (strtotime($user->offline_at) < strtotime("now")) {
            $user->online_status = "offline";
        }
        $user->save();
    }
}