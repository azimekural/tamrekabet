@extends('layouts.app')
 @section('content')
 
    <style>
           input[type=text] {
               width: 200px;
               box-sizing: border-box;
               border: 1px solid #ccc;
               border-radius: 4px;
               font-size: 16px;
               background-color: white;
               background-image: url("{{asset('images/search.png')}}");
               background-position: 10px 10px;
               background-repeat: no-repeat;
               padding: 12px 20px 12px 40px;
               -webkit-transition: width 0.4s ease-in-out;
               transition: width 0.4s ease-in-out;
           }

           input[type=button] {
               background-color: #63b8ff;
               border: 2px solid #ccc;
               color: white;
               border-radius: 4px;
              padding: 12px 8px 12px 8px;
               text-decoration: none;
               margin: 4px 2px;
              
           }
           .header{
               background-color: #f5f5f5;
               width: 100%;
               height: 100px;
               
           }
           .search{
               width: 270px;
               box-sizing: border-box;
               border: 1px solid #ccc;
               border-radius: 4px;
               font-size: 12px;
               background-color:#C0C0C0;
               padding: 12px 8px 12px 8px;
               
           }
           .soldivler{
                width: 270px;
               box-sizing: border-box;
               border: 1px solid #ccc;
               border-radius: 4px;
               font-size: 12px;
               background-color: #f5f5f5;
               padding: 12px 8px 12px 8px;
               
           }
       
           
   </style>
    <hr>
      <div class="header">
          
             
      </div>
     <hr>
     <div class="container">
      

       
       <div class="container-fluid" style="width:100%">
              <div class="row content" style="padding-top: 0px" >
                 <div class="col-sm-3">
                     
                     
                     <div class="search">
                                <div>
                                    <input type="text" name="search" placeholder="Anahtar Kelime"><input type="button"  value="ARA">
                                </div>
                                <div>
                                   <input type="radio" name="gender" value="male"> Tüm İlanda<br>
                                   <input type="radio" name="gender" value="female"> Sadece İlan Başlığında<br>
                                   <input type="radio" name="gender" value="other"> Sadece Firma Adında Ara
                                </div>  
                     </div>
                     <br>
                     <div class="soldivler">
                         <form  >
                             <h4>İllerde Arama</h4>
                             <select class="form-control" name="il_id" id="il_id"   required >
                                                   <option selected disabled>Seçiniz</option>
                                                   @foreach($iller as $il)
                                                   <option  value="{{$il->id}}"  >{{$il->adi}}</option>
                                                   @endforeach
                                                   
                            </select>
                         </form>
                     </div>
                     <div class="soldivler">
                         
                             <h4>İlan Tarihi Aralığı</h4>
                             <p>Başlangıç Tarihi</p>
                              <input type="date" class="form-control datepicker" id="baslangic_tarihi" name="baslangic_tarihi" placeholder="" value="">
                                  <br>
                             <p>Bitiş Tarihi</p>
                              <input type="date" class="form-control datepicker" id="bitis_tarihi" name="bitis_tarihi" placeholder="" value="">
                      
                     </div>
                     <div class="soldivler">
                         
                             <h4>İlan Sektörü</h4>
                             @foreach($sektorler as $sektor)
                              <input type="checkbox" name="sektor[]" value="{{$sektor->id}}"> {{$sektor->adi}}<br>
                             @endforeach
                      
                     </div>
                     <div class="soldivler">
                         
                             <h4>İlan Türü</h4>
                             <input type="radio" name="ilanTuru"><span class="lever"></span>Mal<br>
                             <input type="radio" name="ilanTuru"><span class="lever"></span>Hizmet<br>
                             <input type="radio" name="ilanTuru"><span class="lever"></span>Yapım İşi
                     </div>
                     <div class="soldivler">
                             <h4>İlan Usulü</h4>
                              <input type="radio" name="gender" value="Açık"> Açık<br>
                              <input type="radio" name="gender" value="Belirli İstekler Arasında">Belirli İstekler Arasında<br>
                              <input type="radio" name="gender" value="Başvuru">Başvuru
                             
                     </div>      
                            
                            
                 </div>
                 <div class="col-sm-9" id="auto_load_div">
                         <?php $i=0;?>  
                        <h3>İlanlar</h3>
                        <table id="ilanlar">
                            <tbody>
                                
                            </tbody>
                        </table>
                </div>
                       
                 </div>
           <script>
                
                function getIlanlarFilterOptions(){
                  var opts = {};
                  opts.sektorler = [];
                  $("input[name='sektor']:checked").each(function(){
                    if(this.opts.sektorlerchecked){
                      opts.sektorler.push($(this).val());
                    }
                  });
                  return opts;
                }
              
                var $checkboxes = $("input:checkbox");
                $checkboxes.on("change", function(){
                  alert("mete");
                  var opts = getIlanlarFilterOptions();
                  console.log(opts);
                  //updateIlanlar(opts);
                });
           
           </script> 
                  <script type="text/javascript">
                      
                      function auto_load(){
                            var il_id=$('#il_id').val();
                            var basTar=$('#baslangic_tarihi').val();
                            var bitTar=$('#bitis_tarihi').val();
                            $.ajax({
                              type:"GET",
                              url: "ilanAraFiltre/il="+il_id,
                              data:{il:il_id,bas_tar:basTar,bit_tar:bitTar},
                              cache: false,
                              success: function(data){
                                 console.log(data);
                                 for(var key=0; key < {{$i}};key++)
                                {
                                 $("#ilan"+key).empty();
                                 $("#adi"+key).empty();
                                 $("#hr"+key).hide();  
                                 
                                }
                                for(var key=0; key <Object.keys(data).length;key++)
                                {
                                 
                                 $("#ilan"+key).append(data[key].ilanadi);
                                 $("#adi"+key).append(data[key].yayin_tarihi);
                                 $("#hr"+key).append("<hr />");
                                }
                                 
                              } 
                            });
                    }
                    $('#il_id').change(function(){
                        auto_load();
                    });
                    $('#baslangic_tarihi').change(function(){
                        auto_load();
                    });
                    $('#bitis_tarihi').change(function(){
                        auto_load();
                    });
                      
                  </script>
                  
        <hr>
    </div>
  
@endsection

