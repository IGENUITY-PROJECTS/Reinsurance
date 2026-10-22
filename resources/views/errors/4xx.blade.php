@extends('errors.layout')
@section('code', $exception->getStatusCode())
@section('title', 'Unable To Open This Page')
@section('message', 'Please check the address or return to the portal and try again.')
