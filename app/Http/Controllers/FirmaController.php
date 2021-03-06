<?php
namespace App\Http\Controllers;
use App\Firma;
use App\Sektor;
use App\Il;
use App\IletisimBilgisi;
use App\Adres;
use App\SirketTuru;
use Session;
use File;
use Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class FirmaController extends Controller
{
    public function uploadImage(Request $request) {
        $file = $request->file('logo');
        $file = array('logo' => $request->file('logo'));
        $rules = array('logo' => 'required|mimes:jpeg,bmp,png|max:100000'); //mimes:jpeg,bmp,png and for max size max:10000
        $validator = Validator::make($file, $rules);
        if ($validator->fails()) {
            return Redirect::to('firmaProfili/'.$request->firmaId)->withInput()->withErrors($validator);
        } 
        else {
            if ($request->file('logo')->isValid()) {
                $destinationPath = 'uploads'; // upload path
                $extension = $request->file('logo')->getClientOriginalExtension(); // getting image extension
                $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
                
                $firma = Firma::find($request->id);
                $oldName=$firma->logo;
                $firma->logo = $fileName; 
                $firma->save();
                
                $request->file('logo')->move($destinationPath, $fileName); // uploading file to given path
                // sending back with message
                Session::flash('success', 'Upload successfully');
                File::delete("uploads/$oldName");
                return Redirect::to('firmaProfili/'.$firma->id);
            } 
            else {
                // sending back with error message.
                Session::flash('error', 'uploaded file is not valid');
                return Redirect::to('firmaProfili/'.$request->firmaId)->withInput()->withErrors($validator);
            }
        }
    }
    public function deleteImage($id){
        $item = Firma::findOrFail($id);
        $oldName=$item->logo;
        $item->logo=null;
        $item->save();
        File::delete("uploads/$oldName");
        return Redirect::to('iletisimbilgilerii/'.$item->id);
    }
    public function iletisimAdd(Request $request){
        $firma = Firma::find($request->id);
        
        $iletisim = $firma->iletisim_bilgileri ?: new IletisimBilgisi();
        $iletisim->telefon = $request->telefon;
        $iletisim->fax = $request->fax;
        $iletisim->web_sayfasi = $request->web_sayfasi;
        $firma->iletisim_bilgileri()->save($iletisim);        

        $adres = $firma->adresler()->where('tur_id', '=', '1')->first() ?: new Adres();
        $adres->il_id = $request->il_id;
        $adres->ilce_id = $request->ilce_id;
        $adres->semt_id = $request->semt_id;
        $adres->adres = $request->adres;
        $tur = 1;
        $adres->tur_id = $tur;
        $firma->adresler()->save($adres);
        
        return redirect('firmaProfili/'.$firma->id);
    }
    public function tanitimAdd(Request $request){
        $firma = Firma::find($request->id);
        
        $firma->tanitim_yazisi = $request->tanitim_yazisi;
        $firma->save();        
        return redirect('firmaProfili/'.$firma->id);
    }
    public function maliBilgiAdd(Request $request){
        $firma = Firma::find($request->id);
        $firma->sirket_turu = $request->sirket_turu;
        $firma->save();
        
        $maliBilgi = $firma->mali_bilgiler ?: new \App\MaliBilgi();
        $maliBilgi->unvani = $request->unvani;
        $maliBilgi->vergi_numarasi = $request->vergi_numarasi;
        $maliBilgi->vergi_dairesi_id = $request->vergi_dairesi_id;
        $maliBilgi->sermayesi = $request ->sermayesi;
        $maliBilgi->yillik_cirosu = $request ->yillik_cirosu;
        $maliBilgi->ciro_goster = $request ->ciro_goster;
        $maliBilgi->sermaye_goster = $request ->sermaye_goster;
        $firma->mali_bilgiler()->save($maliBilgi);        
        
        
        $adres = $firma->adresler()->where('tur_id', '=', '2')->first() ?: new Adres();
        $adres->il_id = $request->mali_il_id;
        $adres->ilce_id = $request->mali_ilce_id;
        $adres->semt_id = $request->mali_semt_id;
        $adres->adres = $request->fatura_adres;
        $tur = 2;
        $adres->tur_id = $tur;
        $firma->adresler()->save($adres);
        
        return redirect('firmaProfili/'.$firma->id);
    }
    public function ticariBilgiAdd(Request $request){
        $firma = Firma::find($request->id);
        $firma->kurulus_tarihi=$request->kurulus_tarihi;
        $firma->save();
        
        $ticariBilgi = $firma->ticari_bilgiler ?: new \App\TicariBilgi();
        $ticariBilgi->tic_sicil_no = $request->ticaret_sicil_no;
        $ticariBilgi->tic_oda_id = 1;//$request->ticaret_odasi;
        $ticariBilgi->ust_sektor_id = $request->ust_sektor;
        
        $firma->ticari_bilgiler()->save($ticariBilgi);        
        
        $uretilenMarka = new \App\UretilenMarka();
        foreach($request->firmanin_urettigi_markalar as $urettigiMarka){
        $uretilenMarka->adi = $urettigiMarka;
        $firma->uretilen_markalar()->save($uretilenMarka);
        }
        
        foreach($request->faaliyet_sektorleri as $sektor){
        $firma->sektorler()->attach($sektor);
        }
        foreach($request->firma_departmanları as $departman){
        $firma->departmanlar()->attach($departman);
        }
        foreach($request->firmanin_sattıgı_markalar as $markalar){
        $firma->satilan_markalar()->attach($markalar);
        }
        foreach($request->firma_faaliyet_turu as $faaliyetTur){
        $firma->faaliyetler()->attach($faaliyetTur);
        }
        return redirect('firmaProfili/'.$firma->id);
    }
    public function kaliteAdd(Request $request){
        $firma = Firma::find($request->id);
         
        $firma->kalite_belgeleri()->attach($request->kalite_belgeleri,['belge_no'=>$request->belge_no]);
   
        return redirect('firmaProfili/'.$firma->id);
    }
    public function referansAdd(Request $request){        
        $firma = Firma::find($request->id);
        $firma_referans = $firma->firma_referanslar ?: new \App\FirmaReferans();
            if ($firma->firma_referanslar) {
                $firmaReferans = $firma->firma_referanslar()->where('id', '=', '$request->ref_id')->first() ? : new \App\FirmaReferans();
            } else {
                $firmaReferans = $firma->firma_referanslar()->where('ref_turu', '=', '$request->ref_turu')->first() ? : new \App\FirmaReferans();
            }

        $firmaReferans->ref_turu=$request->ref_turu;
        $firmaReferans->adi=$request->ref_firma_adi;
        $firmaReferans->is_adi=$request->yapılan_isin_adi;
        $firmaReferans->is_turu=$request->isin_turu;
        $firmaReferans->is_yili=$request->is_yili;
        $firmaReferans->calisma_suresi=$request->calısma_suresi;
        $firmaReferans->yetkili_adi=$request->yetkili_kisi_adi;
        $firmaReferans->yetkili_email=$request->yetkili_kisi_email;
        $firmaReferans->yetkili_telefon=$request->yetkili_kisi_telefon;
        $firma->firma_referanslar()->save( $firmaReferans);
        return redirect('firmaProfili/'.$firma->id);
    }
    public function referansUpdate(Request $request){        
        $referans = \App\FirmaReferans::find($request->ref_id);
       
        $referans->ref_turu=$request->ref_turu;
        $referans->adi=$request->ref_firma_adi;
        $referans->is_adi=$request->yapılan_isin_adi;
        $referans->is_turu=$request->isin_turu;
        $referans->is_yili=$request->is_yili;
        $referans->calisma_suresi=$request->calısma_suresi;
        $referans->yetkili_adi=$request->yetkili_kisi_adi;
        $referans->yetkili_email=$request->yetkili_kisi_email;
        $referans->yetkili_telefon=$request->yetkili_kisi_telefon;
        $referans->save( );
        return redirect('firmaProfili/'.$referans->firma_id);
    }
    public function kaliteGuncelle(Request $request){        
        $firma = \App\Firma::find($request->id);
        

        
        $kalite->save();
        return redirect('firmaProfili/'.$kalite->firma_kalite_belgeleri->$firma_id);
    }
    public function brosurUpdate(Request $request){    
        $file = $request->file('yolu');
        
        // getting all of the post data
        $file = array('yolu' => $request->file('yolu'));
        // setting up rules
        $rules = array('yolu' => 'required|mimes:pdf|max:100000'); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        $validator = Validator::make($file, $rules);
        if ($validator->fails()) {
            // send back to the page with the input data and errors
            return Redirect::to('firmaProfili/'.$request->id)->withInput()->withErrors($validator);
        } else {
            // checking file is valid.
            if ($request->file('yolu')->isValid()) {
                $destinationPath = 'brosur'; // upload path
                $extension = $request->file('yolu')->getClientOriginalExtension(); // getting image extension
                $fileName = rand(11111, 99999) . '.' . $extension; // renameing image

                $brosur = \App\FirmaBrosur::find(1);
                $oldName = $brosur->yolu;
                $brosur->yolu=$fileName;
                $brosur->adi=$request->brosur_adi;

                $brosur->save( );
                 $request->file('yolu')->move($destinationPath, $fileName); // uploading file to given path
                // sending back with message
                Session::flash('success', 'Upload successfully');
                File::delete("brosur/$oldName");
                return Redirect::to('firmaProfili/'.$brosur->firma_id);
                //return  Redirect::route('commucations')->with('fileName', $fileName);
            } else {
                // sending back with error message.
                Session::flash('error', 'uploaded file is not valid');
                return Redirect::to('firmaProfili/'.$brosur->firma_id)->withInput()->withErrors($validator);
            }
        }
    }
    public function calisanGunleriAdd(Request $request){
         $validator = Validator::make($request->all(), [
                    'calisma_gunleri' => 'required',
                    'calisma_saatleri' => 'required',
                    'calisma_profili' => 'required',
                    'calisma_sayisi' => 'required',
                    

             
                    
        ]);

        /*if ($validator->fails()) {
            return redirect('firmaProfili/'.$request->id)
                            ->withInput()
                            ->withErrors($validator);
        }*/
        
        $firma = Firma::find($request->id);
        $firma_calisan = $firma->firma_calisma_bilgileri ?: new \App\FirmaCalismaBilgisi();
        $firma_calisan->calisma_gunleri_id=$request->id;
        $firma_calisan->calisma_saatleri=$request->calisma_saatleri;
        $firma_calisan->calisan_profili=$request->calisma_profili;
        $firma_calisan->calisan_sayisi=$request->calisma_sayisi;
       
      $firma->firma_calisma_bilgileri()->save( $firma_calisan);
        return redirect('firmaProfili/'.$firma->id);
    }
    public function bilgilendirmeTercihiAdd(Request $request){
        $firma = Firma::find($request->id);
        $firma->bilgilendirme_tercihi=$request->bilgilendirme_tercihi;
        $firma->save();
        
        return redirect('firmaProfili/'.$firma->id);
    }
    public function uploadPdf(Request $request){
         $file = $request->file('yolu');
        
        // getting all of the post data
        $file = array('yolu' => $request->file('yolu'));
        // setting up rules
        $rules = array('yolu' => 'required|mimes:pdf|max:100000'); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        $validator = Validator::make($file, $rules);
        if ($validator->fails()) {
            // send back to the page with the input data and errors
            return Redirect::to('firmaProfili/'.$request->id)->withInput()->withErrors($validator);
        } else {
            // checking file is valid.
            if ($request->file('yolu')->isValid()) {
                $destinationPath = 'brosur'; // upload path
                $extension = $request->file('yolu')->getClientOriginalExtension(); // getting image extension
                $fileName = rand(11111, 99999) . '.' . $extension; // renameing image

                $firma = Firma::find($request->id);
                $firma_brosur= new \App\FirmaBrosur();
                
                /*if ($firma->firma_brosurler() != " "){
                    $oldName=$firma->firma_brosurler()->first()->yolu ;
     
                }*/
                
                $firma_brosur->yolu = $fileName;
                $firma_brosur->adi=$request->brosur_adi;
                $firma->firma_brosurler()->save($firma_brosur);

                $request->file('yolu')->move($destinationPath, $fileName); // uploading file to given path
                // sending back with message
                Session::flash('success', 'Upload successfully');
                /*if ($firma->firma_brosurler){
                    
                File::delete("brosur/$oldName");
                }*/
                return Redirect::to('firmaProfili/'.$firma->id);
                //return  Redirect::route('commucations')->with('fileName', $fileName);
            } else {
                // sending back with error message.
                Session::flash('error', 'uploaded file is not valid');
                return Redirect::to('firmaProfili/'.$firma->id)->withInput()->withErrors($validator);
            }
        }
         
     }
    public function showFirma($id){
        

        
        $firma = Firma::find($id);
        if (Gate::denies('show', $firma)) {
              return Redirect::to('/');
        }
        //$this->authorize('show',$firma);
        
        $iller = Il::all();
        $sirketTurleri=  SirketTuru::all();
        $vergiDaireleri= \App\VergiDairesi::all();
        $ticaretodasi=  \App\TicaretOdasi::all();
        $ustsektor=  Sektor::all();
        $departmanlar=  \App\Departman::all();
        $markalar=  \App\SatilanMarka::all();
        $faaliyetler= \App\Faaliyet::all();
        $kalite_belgeleri= \App\KaliteBelgesi::all();
        $calisma_günleri= \App\CalismaGunu::all();
        
        return view('Firma.firmaProfili', ['firma' => $firma], ['iller' => $iller])->with('sirketTurleri',$sirketTurleri)->with('vergiDaireleri',$vergiDaireleri)->with('ustsektor',$ustsektor)->with('ticaretodasi',$ticaretodasi)->with('departmanlar',$departmanlar)->with('markalar',$markalar)->with('faaliyetler',$faaliyetler)->with('kalite_belgeleri',$kalite_belgeleri)->with('calisma_günleri',$calisma_günleri);
    }
    public function deleteKalite(Request $request,$id){  
        
         $kalite = \App\FirmaKaliteBelgesi::find($id);
         
         $kalite->delete();
         
        return redirect('firmaProfili/'.$request->firma_id);
    }
      public function deleteReferans(Request $request,$id){  
        
         $referans = \App\FirmaReferans::find($id);
         
         $referans->delete();
         
        return redirect('firmaProfili/'.$request->firma_id);
    }
     public function deleteBrosur(Request $request,$id){  
        
         $brosur = \App\FirmaBrosur::find($id);
         
         $brosur->delete();
         
        return redirect('firmaProfili/'.$request->firma_id);
    }
     
     
    //eski fonksiyonlar...suan kullanılmıyorlar
    public function firma(Request $request){
        $firma = new Firma();
        $firma->adi = $request->firmaAdi;
        $firma->save();
        
        foreach($request->sektor as $sektor)
            $firma->sektorler()->attach($sektor);
        return redirect('/');
    } 
    public function index($id){
        $firmalar = Firma::find($id);
        $sektorler = Sektor::all();

        return view('firmaKaydet')->with('firmalar',$firmalar)->with('sektorler', $sektorler);
    }
    
}