@extends('Master.master')

@section('title','Update Rule')

@section('content')
    <div class="pagetitle">
        <h1>Rules & Regulations Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('RulesUpdate',$rules)}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputName" class="col-sm-2 col-form-label">Language</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLanguage" value="{{$rules->lang}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Content</label>
                        <div class="col-sm-10">
                            {{-- <input type="text" class="form-control" name="inputContent"> --}}
                            {{-- <textarea  name="content" rows="10" cols="80"></textarea> --}}
                            <textarea id="content" name="content" rows="10" cols="80">{{$rules->content}}</textarea>
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

    {{-- <script type="module">
    import {
        ClassicEditor,
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Underline,
        Strikethrough,
        Font,
        Heading,
        Alignment,
        List,
        Indent,
        Link,
        Image,
        Table,
        HtmlEmbed
    } from 'ckeditor5';

    ClassicEditor
        .create(document.querySelector('#content'), {
            plugins: [
                Essentials,
                Paragraph,
                Bold,
                Italic,
                Underline,
                Strikethrough,
                Font,
                Heading,
                Alignment,
                List,
                Indent,
                Link,
                Image,
                Table,
                HtmlEmbed
            ],
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'alignment:left', 'alignment:center', 'alignment:right', 'alignment:justify', '|',
                'link', 'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|',
                'insertTable', 'imageUpload', 'htmlEmbed', '|',
                'undo', 'redo'
            ],
            fontSize: {
                options: [
                    'tiny',
                    'small',
                    'default',
                    'big',
                    'huge'
                ]
            },
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ]
            },
            image: {
                toolbar: [
                    'imageTextAlternative', 'imageStyle:full', 'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn', 'tableRow', 'mergeTableCells'
                ]
            },
            htmlEmbed: {
                showPreviews: true
            }
        })
        .then(editor => {
            window.editor = editor;
        })
        .catch(error => {
            console.error(error);
        }); --}}
</script>





@endsection
