<?php
namespace App\Domain\Repogitories\RESTfulDAO;
interface CommonDAOInterface
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($model);
    public function getAll_My_Data($model);
    public function getAll_ID($model);//idだけ取得
    public function getDataBy($model);//自由に取得
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($model);

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store($model);

    /**
     * Display the specified resource.
     *
     * @param  \App\Tw_Api_Request  $tw_Api_Request
     * @return \Illuminate\Http\Response
     */
    public function show($model);
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Tw_Api_Request  $tw_Api_Request
     * @return \Illuminate\Http\Response
     */
    public function edit($model);

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Tw_Api_Request  $tw_Api_Request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update($model);


    public function destroy($model);
}
