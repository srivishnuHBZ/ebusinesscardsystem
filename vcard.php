<?php 
$color = $_GET['color'];
$file = $_GET['file'];
$name = $_GET['name'];
$position = $_GET['position'];
$phone = $_GET['phone'];
$email = $_GET['email'];
$address = $_GET['address'];
?> 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
      integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="libs/vcard.css">
    <title>vCard</title>
  </head>
  <body>
   <div class="card-wrapper">
     <div class="card color-<?php echo $color;?>">
        <div class="card-front">
          <div class="left">
          <h4><span>+60 3-2809 1220</span></h4>
            <img src="temp/<?php echo $file;?>.png" />
            <h4><span>Scan</span> ME</h4>
          </div>
             <div class="right">
                <div class="person right-content">
                    <i class="fas fa-user-tie"></i>
                      <div class="ht">
                          <h4><?php echo $name;?></h4>
                          <p class="pos"><?php echo $position;?></p>
                      </div>
                </div>
				 <div class="phone right-content">
                    <i class="fas fa-phone"></i>
                      <div>
						  <p><?php echo $phone;?></p>
                      </div>
                </div>
				 <div class="email right-content">
                    <i class="fas fa-envelope-open"></i>
                      <div>
						  <p><?php echo $email;?></p>
                      </div>
                </div>
				 <div class="address right-content">
                    <i class="fas fa-map-marker-alt"></i>
                      <div>
						  <p><?php echo $address;?></p>

                      </div>
                </div>
             </div> 
        </div>
		 	<div class="card-back">
		 		<img src="img/logo.png"/>
		 	</div>
     </div>
   </div>
   <div class="canvas" style="height: 0px;overflow: hidden;">
        <div class="card-wrapper2" id="vcard">
          <div class="card color-<?php echo $color;?>">
             <div class="card-front">
               <div class="left">
                 <img src="temp/<?php echo $file;?>.png" />
                 <p><span>Scan</span> ME</p>
               </div>
                  <div class="right">
                     <div class="person right-content">
                         <i class="fas fa-user-tie"></i>
                           <div class="ht">
                               <p><?php echo $name;?></p>
                               <p class="pos"><?php echo $position;?></p>
                           </div>
                     </div>
             <div class="phone right-content">
                         <i class="fas fa-phone"></i>
                           <div>
                  <p><?php echo $phone;?></p>
                           </div>
                     </div>
             <div class="email right-content">
                         <i class="fas fa-envelope-open"></i>
                           <div>
                  <p><?php echo $email;?></p>
                           </div>
                     </div>
             <div class="address right-content">
                         <i class="fas fa-map-marker-alt"></i>
                           <div>
                  <p><?php echo $address;?></p>

                           </div>
                     </div>
                  </div> 
             </div>
          </div>
        </div>
   </div>
   <a href="#" id="imgD" class="vbtn v-<?php echo $color;?>" download="<?php echo $file;?>.png">Download</a>
  </body>
  <script type="text/javascript" src="libs/jquery-3.5.1.min.js"></script>
  <script type="text/javascript" src="libs/dom-to-image.min.js"></script>
  <script type="text/javascript">

    jQuery(function($){
      $("#imgD").on("click",function(e){
        e.preventDefault();
        var node = document.getElementById('vcard');
        domtoimage.toPng(node,{ height: 300, width: 550 })
        .then(function (dataUrl) {
            var link = document.createElement('a');
             link.download = '<?php echo $file;?>.png';
             link.href = dataUrl;
             link.click();
             link.remove();
        })
        .catch(function (error) {
            console.error('oops, something went wrong!', error);
        });
      });
    });
   

   
  </script>
</html>
