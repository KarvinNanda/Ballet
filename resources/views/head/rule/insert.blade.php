@extends('Master.master')

@section('title','Add Rule')

@section('content')
    <div class="pagetitle">
        <h1>Rules & Regulations Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('RulesAdd')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Language</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLanguage">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Content</label>
                        <div class="col-sm-10">
                            {{-- <input type="text" class="form-control" name="inputContent"> --}}
                            {{-- <textarea  name="content" rows="10" cols="80"></textarea> --}}
                            <textarea id="content" name="content" rows="10" cols="80"></textarea>
                        </div>
                    </div>

                    <div class="justify-content-end d-flex">
                        <button class="btn btn-success p-2 ps-5 pe-5 mb-3">
                            Submit
                        </button>
                    </div>

                    @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{$error}}
                            </div>
                        @endforeach
                    @endif

                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>

    <script>
        // tinymce.init({
        //     selector: '#content', // use the ID of the textarea you want to convert to WYSIWYG
        //     plugins: 'advlist autolink lists link image charmap print preview anchor',
        //     toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
        // });
        CKEDITOR.replace('content', {
        allowedContent: true  // Allows all HTML tags to be retained
    });
    </script>



@endsection
