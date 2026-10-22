@extends('errors.layout')
@section('code', $exception->getStatusCode())
@section('title', 'Temporarily Unavailable')
@section('message', 'We could not complete your request. Please try again later.')
