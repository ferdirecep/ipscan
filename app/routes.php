<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	//IP ve MAC adresleri taraniyor...
	
	//ARP Tablosunu veritabanýna kaydetme kodu
	$sayac=0;
	exec ("arp -a", $results, $return_val);
	for ($i=3;$i<count($results);$i++){
		$bolunmus = explode(" ", $results[$i]);
		$ipmaclist=new Ipmac();
		for ($j=0;$j<count($bolunmus);$j++){
			if ($bolunmus[$j]!="" & $sayac==0) {
				$ip_address=$bolunmus[$j];
				$ipmaclist->ip_address=$ip_address;
				$sayac=1;
			}
			elseif ($bolunmus[$j]!="" & $sayac==1){
				$mac_address=$bolunmus[$j];
				$ipmaclist->mac_address=$mac_address;
				$sayac=2;
			}
			elseif ($bolunmus[$j]!="" & $sayac==2){
				$sayac=0;
			}
		}
		$arptablosu=Ipmac::where('ip_address', 'LIKE', '%'.$ip_address.'%')->get();
		if (!$arptablosu->isEmpty()){
			if ($arptablosu->first()->mac_address==$mac_address) {
				// Bu ip ve mac adresi kayitli!
			}else{
				$arptablosu->mac_address=$mac_address;
				$arptablosu->save();
				echo $ip_address." ip adresinin mac adresi degisti! <br />";
			}
		}else{
			$ipmaclist->save();
			// tabloya eklendi
		}
	}
	//Bu iþlemlerin sonunda arp tablosu kaydedildi!
	
	//ip adresi tarama kodu
	for ($ipa=1; $ipa <= 30; $ipa++){
	   $ip = "192.168.1.".$ipa;
	   $ping_output=array();
	   exec ("ping -n 1 -w 1 $ip 2>&1", $ping_output, $return_val);
	
	   if(stripos($ping_output[2],"TTL")!==false) {
	     echo $ip." - IP Adresine Sahip Makine Bulundu. ";
	     $result=Ip::where("ip_address","=",$ip)->get();
	     if ($result->isEmpty()) {
	     	$ipaddress = new Ip();
	     	$ipaddress->ip_address = $ip;
	     	$arptablosu=Ipmac::where('ip_address', 'LIKE', '%'.$ip.'%')->get();
	     	if (!$arptablosu->isEmpty()){
	     		$ipaddress->mac_address =$arptablosu->first()->mac_address;
	     		$ipaddress->save();
	     		echo "Yeni IP adresi ve Mac adresi Veritabanina Kaydedildi! <br/>";
	     	}else{
	     		$ipaddress->save();
	     		echo "Yeni IP adresi Veritabanina Kaydedildi! MAC adresi BULUNAMADI!<br/>";
	     	}
	     }else{
	     	echo "Veritabaninda kayitli! <br/>";
	     }
	     
	   }
	}
});
