@extends('layouts.public')

@section('content')
    <livewire:public.submission-form :release="$release" :submission="$submission" />
@endsection
