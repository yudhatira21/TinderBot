<?php
error_reporting(0);
include 'curl.php';
define('API', 'https://api.gotinder.com');

menu:
echo "
▀█▀ █ █▄░█ █▀▄ █▀▀ █▀█   █▄▄ █▀█ ▀█▀
░█░ █ █░▀█ █▄▀ ██▄ █▀▄   █▄█ █▄█ ░█░\n";
echo "Created by yudha tira pamungkas\n\n";
headers(null);
account();

echo "List Tools : \n";
echo "[1] Auto Likes Tinder \n";
echo "[2] Auto Likes Tinder By Catalog \n";
echo "[3] Set City \n";
echo "[4] Set Distance And Age \n";
echo "Select Tools : ";
$select = trim(fgets(STDIN));

if ($select == 1) {
    echo "\n====== AUTO LIKES TINDER  ======\n\n";
    echo "If no one new in your area do you want to use random area y/n : ";
    $choice = trim(fgets(STDIN));
    while (true) {
        auto_like($choice);
    }

} elseif ($select == 2) {
    echo "\n====== AUTO LIKES TINDER BY CATALOG ======\n\n";
    echo "If no one new in your area do you want to use random area y/n : ";
    $choice = trim(fgets(STDIN));
    while (true) {
        auto_like_catalog($choice);
    }
} elseif ($select == 3) {
    echo "\n====== SET CITY TINDER PROFILE ======\n\n";
    echo "Enter the city name, for example - Bandung : ";
    $nama = trim(fgets(STDIN));

    set_city($nama);
} elseif ($select == 4) {
    echo "\n====== SET DISTANCE AND AGE ======\n\n";

    echo "Distance Preference - Max 161 : ";
    $distance = trim(fgets(STDIN));

    echo "Age Filter Min - Min 18 : ";
    $min = trim(fgets(STDIN));

    echo "Age Filter Max - Max 100 : ";
    $max = trim(fgets(STDIN));

    set_preference($distance, $min, $max);
} else {
    goto menu;
}

function headers($name) {
    $tokenFile = "token.txt";

    if (file_exists($tokenFile)) {
        $token = file($tokenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } else {
        echo "Input Auth Token : ";
        $token = trim(fgets(STDIN));
        file_put_contents($tokenFile, $token);
    }

    $headers = array(
        "Host: api.gotinder.com",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/114.0",
        "Accept: application/json",
        "Accept-Language: en,en-US",
        "Accept-Encoding: gzip, deflate, br",
        "Referer: https://tinder.com/",
        "app-session-id: ab130225-4189-47a4-98f0-4656354daebc",
        "app-session-time-elapsed: 194481",
        "app-version: 1042500",
        "persistent-device-id: f4c8ae34-253e-4fc8-8514-bb89b2ea3710",
        "tinder-version: 4.25.0",
        "user-session-id: 7f772ad1-2d32-4827-9e05-071ef80307fb",
        "user-session-time-elapsed: 197469",
        "x-supported-image-formats: webp,jpeg",
        "platform: web",
        "X-Auth-Token: ".$token[0],
        "support-short-video: 1",
        "Origin: https://tinder.com",
        "Sec-Fetch-Dest: empty",
        "Sec-Fetch-Mode: no-cors",
        "Sec-Fetch-Site: cross-site",
        "Pragma: no-cache",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "TE: trailers",
        "$name"
    );

    return $headers;
}

function account() {

    $profil = curl(API.'/v2/profile?locale=en&include=account,available_descriptors,boost,bouncerbypass,contact_cards,email_settings,feature_access,instagram,likes,profile_meter,notifications,misc_merchandising,offerings,onboarding,plus_control,purchase,readreceipts,spotify,super_likes,tinder_u,travel,tutorials,user,paywalls', null, headers(null), false);

    $json = json_decode($profil, true);

    $account_type = $json['data']['purchase']['purchases'][0]['product_type'];
    $bio = $json['data']['user']['bio'];
    $name = $json['data']['user']['name'];
    $birth_date = $json['data']['user']['birth_date'];
    $age = explode('T', $birth_date);

    echo "========================================================\n";
    echo "Hello ".$name.", ".countage($age[0])."\n";

    if ($account_type != "") {
        echo "Account type : ".$account_type."\n";
    } else {
        echo "Account type : free\n";
    }
    echo "========================================================\n\n";
}



function set_preference($distance, $min, $max) {

    echo "\nSet Distance to ".$distance."KM \n";
    echo "Set Age Filter Min ".$min." \n";
    echo "Set Age Filter Max ".$max." \n";

    
    $set = curl(API.'/v2/profile?locale=en', '{"user":{"distance_filter":'.$distance.',"age_filter_min":'.$min.',"age_filter_max":'.$max.'}}', headers(null), true);

    $age = curl(API.'/v2/profile?locale=en', '{"user":{"age_filter_min":'.$min.',"age_filter_max":'.$max.'}}', headers(null), true);

    if (stripos($set, '"status":200')) {
        echo "\nSuccess set preference\n";
    } else {
        echo "Failed\n";
    }

}


function set_city($nama) {

    $search = curl(API.'/location/search?locale=id&s='.$nama, null, headers(null), false);
    
    $json = json_decode($search, true);

    if (stripos($search, 'AccessForbidden›missing Tinder Plus')) {
        echo $json['error']."\n";
        close();
    } else {
        echo "\nSet city to ".$json['results'][0]['administrative_area_level_1']['long_name']."\n";
        echo "\nLat : ".$json['results'][0]['lat'];
        echo "\nLon : ".$json['results'][0]['lon'];
        
        $set = curl(API.'/passport/user/travel?locale=en', '{"lat":'.$json['results'][0]['lat'].',"lon":'.$json['results'][0]['lon'].'}', headers('Content-Type: application/json'), true);

        if (stripos($set, '"unlimited":true')) {
            echo "\nSuccess set city to : ".$json['results'][0]['administrative_area_level_1']['long_name']."\n";
        } else {
            echo "\nFailed set city to : ".$nama."\n";
        }
    }

}


function random_city() {
    $kota = array(
       // Jawa Barat
        "Bandung", "Bandung Barat", "Bekasi", "Bogor", "Ciamis", "Cianjur", "Cirebon", "Garut", "Indramayu", "Karawang",
        "Kuningan", "Majalengka", "Pangandaran", "Purwakarta", "Subang", "Sukabumi", "Sumedang", "Tasikmalaya", "Banjar", "Cimahi",
        "Cirebon", "Depok", "Karawang", "Kuningan", "Majalengka", "Pangandaran", "Purwakarta", "Subang", "Sukabumi", "Sumedang",
        "Tasikmalaya",

        // Jawa Tengah
        "Banjarnegara", "Banyumas", "Batang", "Blora", "Boyolali", "Brebes", "Cilacap", "Demak", "Grobogan", "Jepara",
        "Karanganyar", "Kebumen", "Kendal", "Klaten", "Kudus", "Magelang", "Pati", "Pekalongan", "Pemalang", "Purbalingga",
        "Purworejo", "Rembang", "Semarang", "Sragen", "Sukoharjo", "Tegal", "Temanggung", "Wonogiri", "Wonosobo",

        // Daerah Istimewa Yogyakarta
        "Bantul", "Gunung Kidul", "Kulon Progo", "Sleman",

        "Bangkalan", "Banyuwangi", "Blitar", "Bojonegoro", "Bondowoso", "Gresik", "Jember", "Jombang", "Kediri", "Lamongan",
        "Lumajang", "Madiun", "Magetan", "Malang", "Mojokerto", "Nganjuk", "Ngawi", "Pacitan", "Pamekasan", "Pasuruan",
        "Ponorogo", "Probolinggo", "Sampang", "Sidoarjo", "Situbondo", "Sumenep", "Trenggalek", "Tuban", "Tulungagung",
        "Batu", "Blitar", "Kediri", "Madiun", "Malang", "Mojokerto", "Pasuruan", "Probolinggo", "Surabaya",

        // Banten
        "Lebak", "Pandeglang", "Serang", "Tangerang",
        "Cilegon", "Serang", "Tangerang", "Tangerang Selatan",

        // DKI Jakarta
        "Jakarta Barat", "Jakarta Pusat", "Jakarta Selatan", "Jakarta Timur", "Jakarta Utara"
    );

    $random = array_rand($kota);

    return $kota[$random];
}

function auto_like($choice) {

    $list = curl(API.'/v2/recs/core?locale=en', null, headers(null), false);
    $json = json_decode($list, true);

    $result = $json['data']['results'];


    if ($result == null) {

        if ($choice == 'y') {
            echo "We've run out of potential matches in your area. Go global and see people around the world. You can turn off global profiles in your settings at any time.\n";
            echo "Try to set random city\n";
            set_city(random_city());
        } else {
             echo "We've run out of potential matches in your area. Go global and see people around the world. You can turn off global profiles in your settings at any time.\n";
            close();
        }
       
    } else {
        $count = count($result);
        for ($a=0; $a < $count ; $a++) { 
            $photoId = $json['data']['results'][$a]['user']['photos'][0]['id'];
            $s_number = $json['data']['results'][$a]['s_number'];
            $school = $json['data']['results'][$a]['teaser']['string'];
            $content_hash = $json['data']['results'][$a]['content_hash'];
            $nama = $json['data']['results'][$a]['user']['name'];
            $id = $json['data']['results'][$a]['user']['_id'];
            $city = $json['data']['results'][$a]['user']['city']['name'];
            $umur = $json['data']['results'][$a]['user']['birth_date'];
            $age = explode('T', $umur);
            $bio = $json['data']['results'][$a]['user']['bio'];
            $distance = $json['data']['results'][$a]['ui_configuration']['id_to_component_map']['distance']['text_v1']['content'];


            $like = curl(API.'/like/'.$id, '{"photoId":"'.$photoId.'","content_hash":"'.$content_hash.'","super":0,"rec_traveling":true,"fast_match":0,"top_picks":0,"undo":0,"s_number":'.$s_number.',"liked_content_id":"'.$photoId.'","liked_content_type":"photo"}', headers(null), false);

            if (stripos($like, '"status":200')) {
                $match = fetch_value($like, '"match":',',"likes_remaining"');

                echo "\n========================================================\n";
                echo "Nama          : ".$nama."\n";
                if ($bio != "") {
                    echo "Bio           : ".$bio."\n";
                } else {
                    echo "Bio           : -\n";
                }

                if ($school != "") {
                    echo "School or Work: ".$school."\n";
                } else {
                    echo "School or Work: -\n";
                }

                echo "Umur          : ".countage($age[0])." Tahun\n";

                if ($city != "") {
                    echo "City          : ".$city."\n";
                } else {
                    echo "City          : -\n";
                }

                if ($match == "true") {
                    echo "Match         : Yes\n";
                } else {
                    echo "Match         : No\n";
                }

                if ($distance != "") {
                    echo "Distance      : ".$distance."\n";
                } else {
                    echo "Distance      : -\n";
                }
                echo "========================================================\n";

            } else {
                echo "Failed\n";
            }

        }
    }

    
}


function auto_like_catalog($choice) {

    $explorer = curl(API.'/v2/explore', null, headers(null), false);
    $json_explorer = json_decode($explorer, true);


    $catalog = $json_explorer['data']['catalog_groups'][2]['sections'][0]['items'];
    $count_catalog = count($catalog);

    for ($i=0; $i < $count_catalog; $i++) { 
        $title = $json_explorer['data']['catalog_groups'][2]['sections'][0]['items'][$i]['title'];
        $catalog_id = $json_explorer['data']['catalog_groups'][2]['sections'][0]['items'][$i]['catalog_id'];


        $list = curl(API.'/v2/explore/recs?locale=in&catalog_id='.$catalog_id.'&distance_setting=km', null, headers(null), false);
        $json = json_decode($list, true);


        $result = $json['data']['results'];

        if ($result == null) {
            if ($choice == 'y') {
                echo "We've run out of potential matches in your area. Go global and see people around the world. You can turn off global profiles in your settings at any time.\n";
                echo "Try to set random city\n";
                set_city(random_city());
            } else {
                 echo "We've run out of potential matches in your area. Go global and see people around the world. You can turn off global profiles in your settings at any time.\n";
                close();
            }
        } else {
            $count = count($result);

            echo "\nCatalog         : ".$title."\n";
            for ($a=0; $a < $count ; $a++) { 
                $photoId = $json['data']['results'][$a]['user']['photos'][0]['id'];
                $s_number = $json['data']['results'][$a]['s_number'];
                $school = $json['data']['results'][$a]['teaser']['string'];
                $content_hash = $json['data']['results'][$a]['content_hash'];
                $nama = $json['data']['results'][$a]['user']['name'];
                $id = $json['data']['results'][$a]['user']['_id'];
                $city = $json['data']['results'][$a]['user']['city']['name'];
                $umur = $json['data']['results'][$a]['user']['birth_date'];
                $age = explode('T', $umur);
                $bio = $json['data']['results'][$a]['user']['bio'];
                $distance = $json['data']['results'][$a]['ui_configuration']['id_to_component_map']['distance']['text_v1']['content'];


                $like = curl(API.'/like/'.$id, '{"photoId":"'.$photoId.'","content_hash":"'.$content_hash.'","super":0,"rec_traveling":true,"fast_match":0,"top_picks":0,"undo":0,"s_number":'.$s_number.',"liked_content_id":"'.$photoId.'","liked_content_type":"photo","explore_catalog_id":"'.$catalog_id.'"}', headers(null), false);

                if (stripos($like, '"status":200')) {
                    $match = fetch_value($like, '"match":',',"likes_remaining"');


                    echo "\n========================================================\n";
                    echo "Nama          : ".$nama."\n";
                    if ($bio != "") {
                        echo "Bio           : ".$bio."\n";
                    } else {
                        echo "Bio           : -\n";
                    }

                    if ($school != "") {
                        echo "School or Work: ".$school."\n";
                    } else {
                        echo "School or Work: -\n";
                    }

                    echo "Umur          : ".countage($age[0])." Tahun\n";

                    if ($city != "") {
                        echo "City          : ".$city."\n";
                    } else {
                        echo "City          : -\n";
                    }

                    if ($match == "true") {
                        echo "Match         : Yes\n";
                    } else {
                        echo "Match         : No\n";
                    }

                    if ($distance != "") {
                        echo "Distance      : ".$distance."\n";
                    } else {
                        echo "Distance      : -\n";
                    }
                    echo "========================================================\n";

                } else {
                    echo "Rate Limit\n";
                }
            }

        }
        

    }
}


?>
