@extends('layouts.master')

@section('main')
    <style>
        li {
            list-style-position: inside;
        }
    </style>

    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col m-lg-5">
            <div class="text-center" style="background-color: orangered; padding: 20px; color: white; border-radius: 5px">
                <h2>SIN KOLOR</h2>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
    <div class="row mt-5">
        <div class="col-3"></div>
        <div class="col text-center">
            <ul>
                <li><h4>Lager prodavnice</h4></li>
                <li><h4>Evidencija ulaznih računa</h4></li>
                <li><h4>Evidencija dnevnog prometa</h4></li>
            </ul>
        </div>
        <div class="col-3"></div>
    </div>



@endsection
