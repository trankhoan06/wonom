<?php 
    $data = !empty($template_args['data']) ?$template_args['data'] :[];
    if(empty($data)){
        return;
    }


    $groupItems = $data["group_items"];
    $sectionTitle = $data["title"];

    $sectionImageInfo = wp_get_attachment_image_src($data["image"],'
        full');
    $sectionImage = $sectionImageInfo[0];
    $sectionImageW = $sectionImageInfo[1];
    $sectionImageH = $sectionImageInfo[2];

    $numberGroup=[];
    $svgNum=[];

    $index=0;

    foreach ($groupItems  as $key => $group) {
        foreach ($group["items"] as $value) {
            $index++;

            if(empty($numberGroup[$key])){
                $numberGroup[$key]= [
                    "title"=>$group["title"],
                    "color"=>$group["color"],
                    "number"=>[]
                ];
            }

            $image = !empty($value["image"])? wp_get_attachment_image_src($value["image"],'full')[0]:"";

            $link =  "";
            if(!empty($image)){
                $link = 'href="'.$image.'" data-fancybox="utilities" data-caption="'.$value["title"].'" ';
            }

            $numberGroup[$key]["number"][]= '<div><a  '.$link .' class="item" data-index="'.$key.$index.'"><span>'. $value["number"] .'</span> '. $value["title"] .'</a></div>';


            foreach ($value["coordinates"] as  $position) {
                $position = explode(",", $position);
                $y= count($position)>1?trim($position[0]):0;
                $x= count($position)>1?trim($position[1]):0;

                $svgNum[]=  ["x"=>$x,"y"=>$y,"number"=>$value["number"],"title"=>$value["title"],"image"=>$image,"link"=>$attachment,"popupposition"=>$value["popupposition"],"index"=>$key.$index,"color"=>$group["color"]];
            }

        }
    }




?>

<div class="relative utilities-image-wrapper image-map-svg fullpage-map-wrapper" data-w="<?= $sectionImageW ?>" data-h="<?= $sectionImageH ?>">
    <div class="fullpage-map relative" >
        <img class="img-fluid w-100 " src="<?= $sectionImage ?>" >
        <svg width="<?= $sectionImageW ?>" height="<?= $sectionImageH ?>" class="utilities-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 <?= $sectionImageW ?> <?= $sectionImageH ?>">
        <?php foreach ($svgNum as $key => $value) {
            $placement= "top";
            $s=14;
            $popup = '<div class="subdivision-maker"><h5 class="font-2">'.$value["title"].'</h5></div>';
            $popupImage ="";
            $placement= !empty($value["popupposition"])?$value["popupposition"]:"top";

            if(!empty($value["image"])){
                $popup = '<div class="subdivision-maker utilitie-maker"><img src="'.$value["image"].'" alt="'.$value["title"].'"/><h5>'.$value["title"].'</h5></div>';
            }
        ?>
          <g class="item-maker" style="--baseColor:<?= $value["color"] ?>"  transform="translate(<?= $value["x"] - $s  ?>,<?= $value["y"] - $s ?>)" data-index="<?= $value["index"] ?>" data-link="<?= $value["link"] ?>"  data-title="<?= htmlentities($popup) ?>" data-placement="<?= $placement ?>"  data-x="<?= $value["x"] ?>" data-y="<?= $value["y"]?>">
              <g class="ani invisible font-3">
                <circle class="c2" cx="14" cy="14" r="14" />
                <text x="14" y="14" alignment-baseline="middle" text-anchor="middle" dy=".14em"><?= $value["number"] ?></text>
              </g>
            </g>
          <?php } ?>
        </svg>
        <div class="map-tooltip "></div>
    </div>
</div>

<div class="section-content-wrapper section-content-fitbottom  point-event-none div_zindex section-padding pb-lg-0">
    <div class="container">
        <h2 class="section-title text-center mb-0">
            <small><?= __("Mặt bằng tiện ích","tbs") ?></small><br>
            <strong><?= $sectionTitle ?></strong>
        </h2>
    </div>
</div>
<div class="section-content-number section-content-fitleft point-event-none div_zindex">
    <div class="section-content-inner section-padding">
        <div class="section-content do-nicescrol">
            <?php $accId = uniqid(); ?>
            <div class="accordion " id="accordion_<?= $accId ?>">
                <?php
                $groupIndex=0;
                foreach ($numberGroup as $key => $value) {
                    $groupIndex++;
                ?>
                <div class="accordion-item" style="--baseColor:<?= $value["color"] ?>" >
                    <h2 class="accordion-header" id="heading<?= $key ?>">
                      <button class="accordion-button font-2 <?= $groupIndex==1?"":"collapsed" ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $key ?>" aria-expanded="<?= $groupIndex==1?"true":"false" ?>" aria-controls="collapse<?= $key ?>">
                        <?= $value["title"] ?>
                      </button>
                    </h2>
                    <div id="collapse<?= $key ?>" class="accordion-collapse collapse <?= $groupIndex==1?"show":"" ?>" aria-labelledby="headingOne" data-bs-parent="#accordion_<?= $accId ?>">
                      <div class="accordion-body">
                            <div class="items utilities-items font-3">
                            <?= implode("",$value["number"]); ?>
                            </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>

            </div>

        </div>
    </div>
</div>

