@extends('layouts.guest-layout') 

{{-- I've added one new style here for the side image --}}
<style> 
    /* This new rule makes the image fill its column completely */
    .side-image-fill {
        width: 100%;
        height: 100%;
        object-fit: cover;
        min-height: 100vh;
    }
    
    /* Your original button styles are perfect and remain unchanged */
    .btn-outline-primary { 
        color: #448aff; 
        border-color: #448aff; 
        background-color: white; 
    }
    .btn-outline-primary:hover { 
        background-color: #333; 
        color: white; 
        border-color: #448aff; 
    }
    .btn-check:checked+.btn-outline-primary { 
        background-color: #448aff !important; 
        color: white !important; 
        border-color: #448aff !important; 
    } 
</style> 

@section('content') 

{{-- CHANGE 1: Added g-0 to remove gutters and h-100 to make the row full height --}}
<div class="container-fluid p-0 h-100">
    <div class="row g-0 h-100"> 

    {{-- column for side image --}} 
    <div class="col-md-6 d-none d-lg-block"> 
        {{-- CHANGE 2: Added the new side-image-fill class and kept img-fluid --}}
        <img src="{{ asset('images/side-images/annie-unsplash.jpg') }}" class="img-fluid side-image-fill" alt="Photo by Annie Spratt on Unsplash"> 
    </div> 


    {{-- Login Card Column --}}
    <div class="col-12 col-lg-6 d-flex justify-content-center align-items-center bg-light">
        <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Register</h3>
                
                    @if ($errors->any()) 
                        <div class="alert alert-danger mb-4"> 
                            <ul class="mb-0 ps-3"> 
                                @foreach ($errors->all() as $error) 
                                    <li>{{ $error }}</li> 
                                @endforeach 
                            </ul> 
                        </div> 
                    @endif 

                    <form method="POST" action="{{ route('register') }}"> 
                        @csrf 

                        <div class="mb-4 text-center"> 
                            <label class="form-label d-block">{{ __('Register as') }}</label> 
                            <div class="btn-group" role="group" aria-label="Register as role"> 
                                <input type="radio" class="btn-check" name="role" id="role_student" value="student" 
                                    autocomplete="off" @if (old('role', 'student') == 'student') checked @endif> 
                                <label class="btn btn-outline-primary" for="role_student">🎓 Student</label> 

                                <input type="radio" class="btn-check" name="role" id="role_instructor" 
                                    value="instructor" autocomplete="off" @if (old('role') == 'instructor') checked @endif> 
                                <label class="btn btn-outline-primary" for="role_instructor">🧑‍🏫 Instructor</label> 
                            </div> 
                        </div> 

                        <div id="instructor-fields" style="display: none;"> 
                            <div class="mb-3"> 
                                <label for="headline" class="form-label">Profession / Headline</label> 
                                <input type="text" name="headline" id="headline" class="form-control" 
                                    value="{{ old('headline') }}" placeholder="e.g., Web Developer, English Teacher"> 
                            </div> 
                            <div class="mb-3"> 
                                <label for="website_url" class="form-label">Portfolio/LinkedIn URL</label> 
                                <input type="url" name="website_url" id="website_url" class="form-control" 
                                    value="{{ old('website_url') }}" placeholder="Link to your work or profile"> 
                            </div> 
                        </div> 
                        <div class="mb-3"> 
                            <label for="name" class="form-label">{{ __('Name') }}</label> 
                            <input id="name" class="form-control" type="text" name="name" 
                                value="{{ old('name') }}" required autofocus autocomplete="name" /> 
                        </div> 

                        <div class="mb-3"> 
                            <label for="email" class="form-label">{{ __('Email') }}</label> 
                            <input id="email" class="form-control" type="email" name="email" 
                                value="{{ old('email') }}" required /> 
                        </div> 

                        <div class="mb-3"> 
                            <label for="password" class="form-label">{{ __('Password') }}</label> 
                            <input id="password" class="form-control" type="password" name="password" required 
                                autocomplete="new-password" /> 
                        </div> 

                        <div class="mb-3"> 
                            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label> 
                            <input id="password_confirmation" class="form-control" type="password" 
                                name="password_confirmation" required autocomplete="new-password" /> 
                        </div> 

                        <div class="d-flex justify-content-end align-items-center mt-4"> 
                            <a class="text-decoration-none me-3" href="{{ route('login') }}"> 
                                {{ __('Already registered?') }} 
                            </a> 

                            <button type="submit" class="btn bg-default-blue text-white"> 
                                {{ __('Register') }} 
                            </button> 
                        </div> 
                    </form> 

            </div>
        </div>
    </div>
    </div> 
</div> 

@endsection 

{{-- CHANGE 4: Moved your original script into the conventional @push stack --}}
@push('scripts')
<script> 
    document.addEventListener('DOMContentLoaded', function() { 
        const roleRadios = document.querySelectorAll('input[name="role"]'); 
        const instructorFields = document.getElementById('instructor-fields'); 

        function toggleInstructorFields() { 
            const selectedRole = document.querySelector('input[name="role"]:checked').value; 
            if (selectedRole === 'instructor') { 
                instructorFields.style.display = 'block'; 
            } else { 
                instructorFields.style.display = 'none'; 
            } 
        } 
        
        // Run the function on page load to handle validation errors 
        toggleInstructorFields(); 

        // Add an event listener to each radio button 
        roleRadios.forEach(radio => { 
            radio.addEventListener('change', toggleInstructorFields); 
        }); 
    }); 
</script>
@endpush