<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function adminTeacherView(){
        $teachers = Teacher::all();
        return view('adminTeacherView',compact('teachers'));
    }

    public function adminTeacherForm(){
        return view('teacherForm');
    }

    public function deleteTeacher(Request $req){
        $teacher = Teacher::where('TeacherId','=',$req->id);
        $teacher->delete();
        return redirect(route('adminTeacherView'));
    }

    public function adminTeacherFormSubmit(Request $req){
        $teacher = new Teacher();
        $teacher->name = $req->inputName;
        $teacher->Email = $req->inputEmail;
        $teacher->Dob = $req->inputDOB;
        $teacher->Address = $req->inputAddress;
        $teacher->Phone = $req->inputPhone;
        $teacher->Salary = $req->inputSalary;
        $teacher->save();
        return redirect('/adminTeacherView');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        //
    }
}
