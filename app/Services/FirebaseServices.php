<?php

namespace App\Services;

class FirebaseServices
{
    public static function insertNew(String $collection, array $data)
    {
        $collectionRef = app('firebase.firestore')->database()->collection($collection)->newDocument();
        return $collectionRef->set($data);
    }
}
