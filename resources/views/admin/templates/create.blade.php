@extends('layouts.app')
@section('content')
@include('admin.templates.form', ['template' => $template, 'schema' => $schema, 'action' => route('templates.store'), 'method' => 'POST'])
@endsection
