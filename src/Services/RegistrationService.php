<?php
/**
 * Created by PhpStorm.
 * User: haris
 * Date: 7.6.17
 * Time: 00:01
 */

namespace Ibuntu\Services;


use Ibuntu\Models\Department;
use Ibuntu\Models\Faculty;
use Ibuntu\Models\University;

class RegistrationService
{
    function __construct()
    {
    }

    public function createUniversity($uniName, $uniAddress, $uniCity, $uniCountry, $uniSite, $uniEmail){
        /** @var University $university */
        $university = University::create([
            'name' => $uniName,
            'street_address' => $uniAddress,
            'city' => $uniCity,
            'country' => $uniCountry,
            'email' => $uniEmail,
            'uni_website' => $uniSite
        ]);
        return $university->serialize();
    }

    public function getUniversities(){
        /** @var University[] $universities */
        $universities = University::find('all', ['include' => ['faculty' => ['include' => 'department']]]);
        $serializedUniversities = [];
        foreach($universities as $university){
            $serializedUniversities[] = $university->serialize();
        }
        return $serializedUniversities;
    }

    public function createFaculty($uniId, $facultyName, $facultyBranch, $facultyInfo){
        /** @var Faculty $faculty */
        $faculty = Faculty::create([
            "university_id" => $uniId,
            "name" => $facultyName,
            "branch" => $facultyBranch,
            "info" => $facultyInfo
        ]);

        return $faculty->serialize();
    }

    public function createDepartment($uniId, $facultyId, $departmentName, $departmentInfo){
        /** @var Department $faculty */
        $department = Department::create([
            "university_id" => $uniId,
            "faculty_id" => $facultyId,
            "name" => $departmentName,
            "info" => $departmentInfo
        ]);

        return $department->serialize();
    }
}