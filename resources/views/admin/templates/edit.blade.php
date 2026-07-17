@extends('layouts.app')
@section('content')
@include('admin.templates.form', ['template' => $template, 'schema' => $schema, 'action' => route('templates.update', $template), 'method' => 'PUT'])
@endsection
