<?php


namespace Src\Reports\Legacy;


use App\Models\Legacy\Animal;

class AnimalFormatter
{

   static function getDisplayEdad(Animal $animal){

        if ($animal->getFechaNacimiento()==null){
            return null;
        }

        $date1 = new \DateTime($animal->getFechaNacimiento());
        $date2 = new \DateTime(date('c'));
        $diff = $date1->diff($date2);

        $years = $diff->y;
        $months = $diff->m;
        $days = $diff->d;

        if ($years > 0){
            return sprintf("%dA, %dM, %dD",$years,$months,$days);
        }
        else if ($months > 0){
            return sprintf("%dM, %dD",$months,$days);
        }
        else {
            return sprintf("%dD",$days);
        }
    }
}
