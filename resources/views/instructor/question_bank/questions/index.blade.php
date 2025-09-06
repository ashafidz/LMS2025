@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <p class="m-b-10">Kelola pertanyaan untuk topik ini.</p>
                            <h5 class="m-b-0" style="font-size: 2rem;">
                                Topik: {{ $topic->name }}

                            </h5>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                            <li class="breadcrumb-item"><a href="#!">{{ Str::limit($topic->name, 20) }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->
        <div class="pcoded-inner-content">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>List Pertanyaan</h5>
                                        <span>Berikut adalah daftar semua pertanyaan untuk topik ini.</span>
                                        <div class="card-header-right">
                                            <button type="button" class="btn btn-info  ml-2 " data-toggle="modal" data-target="#topicInfoModal" title="Informasi Topik">
                                    <i class="fa fa-info-circle text-white"></i>
                                    Informasi Topik
                                </button>
                                            {{-- UPDATE: This button now triggers the modal --}}
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectQuestionTypeModal"><i class="bi bi-plus-lg text-white"></i>Buat Pertanyaan</button>
                                        </div>
                                    </div>
                                    <div class="card-block table-border-style">
                                        @if (session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Question</th>
                                                        <th>Type</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($questions as $question)
                                                        @php
                                                            // Check if the question is used in any quiz.
                                                            // This assumes your controller uses `with('quizzes')`.
                                                            $isLocked = $question->quizzes->isNotEmpty();
                                                        @endphp
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration + $questions->firstItem() - 1 }}</th>
                                                            <td>{{ Str::limit(strip_tags($question->question_text), 80) }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</td>
                                                            <td class="text-center">
                                                                @if ($isLocked)
                                                                    <label class="label label-danger">Locked</label>
                                                                @else
                                                                    <label class="label label-success">Editable</label>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                {{-- Show Edit button only if not locked --}}
                                                                @if (!$isLocked)
                                                                    <a href="{{ route('instructor.question-bank.questions.edit', $question->id) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                                                @endif

                                                                {{-- Move Question Button --}}
                                                                <button type="button" class="btn btn-info btn-sm" onclick="openMoveModal({{ $question->id }}, '{{ Str::limit(strip_tags($question->question_text), 50) }}')"><i class="fas fa-arrows-alt"></i></button>

                                                                {{-- Always show Clone button --}}
                                                                <form action="{{ route('instructor.question-bank.questions.clone', $question->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-warning btn-sm text-dark"><i class="fa fa-clone"></i></button>
                                                                </form>

                                                                {{-- Disable Delete button if locked --}}
                                                                <form action="{{ route('instructor.question-bank.questions.destroy', $question->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm" {{ $isLocked ? 'disabled' : '' }} title="{{ $isLocked ? 'This question is locked and cannot be deleted.' : '' }}"><i class="fas fa-trash"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">No questions found for this topic.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $questions->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
                <div id="styleSelector"></div>
            </div>
        </div>
    </div>

    <!-- ADDITION: Modal for Selecting Question Type -->
    <div class="modal fade" id="selectQuestionTypeModal" tabindex="-1" role="dialog" aria-labelledby="selectQuestionTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectQuestionTypeModalLabel">Choose Question Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please select the type of question you would like to create.</p>
                    <div class="list-group">
                        {{-- Link to create Multiple Choice (Single) --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'multiple_choice_single']) }}" class="list-group-item list-group-item-action">
                            <strong>Multiple Choice (Single Answer)</strong>
                            <br><small>Students can only select one correct answer from a list.</small>
                        </a>
                        {{-- Link to create Multiple Choice (Multiple) --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'multiple_choice_multiple']) }}" class="list-group-item list-group-item-action">
                            <strong>Multiple Choice (Multiple Answers)</strong>
                            <br><small>Students can select more than one correct answer.</small>
                        </a>
                        {{-- Link to create True/False --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'true_false']) }}" class="list-group-item list-group-item-action">
                            <strong>True / False</strong>
                            <br><small>A simple question with "True" or "False" as answers.</small>
                        </a>
                        {{-- Link to create Drag & Drop --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'drag_and_drop']) }}" class="list-group-item list-group-item-action">
                            <strong>Drag and Drop (Fill in the Blanks)</strong>
                            <br><small>Students drag words into the correct blanks in a sentence.</small>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Move Question Modal -->
    <div class="modal fade" id="moveQuestionModal" tabindex="-1" role="dialog" aria-labelledby="moveQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moveQuestionModalLabel">Pindah Pertanyaan ke Topik Lain</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeMoveModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="moveQuestionForm" method="POST" onsubmit="return validateForm()">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Pertanyaan:</strong> <span id="questionPreview"></span>
                        </div>

                        {{-- Filter untuk topik --}}
                        <div class="form-group">
                            <label for="topic_filter">Filter Topik Berdasarkan Kursus:</label>
                            <select id="topic_filter" class="form-control" onchange="filterTopics()">
                                <option value="all">Tampilkan Semua Topik Saya</option>
                                <option value="global">Topik yang muncul di Semua Kursus Saya</option>
                                <optgroup label="Kursus Spesifik">
                                    @if(isset($courses))
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                                        @endforeach
                                    @endif
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="target_topic">Pilih Topik Tujuan:</label>
                            <div id="topicsList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                {{-- Topics will be loaded here via JavaScript --}}
                            </div>
                        </div>

                        <input type="hidden" id="selectedTopicId" name="target_topic_id" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeMoveModal()">Batal</button>
                        <button type="submit" class="btn btn-primary" id="moveButton" disabled>Pindah Pertanyaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- NEW: Topic Information Modal -->
    <div class="modal fade" id="topicInfoModal" tabindex="-1" role="dialog" aria-labelledby="topicInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="topicInfoModalLabel">
                        <i class="fa fa-info-circle text-info"></i> Informasi Topik
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Nama Topik -->
                            <div class="form-group">
                                <label><strong>Nama Topik:</strong></label>
                                <p class="form-control-static">{{ $topic->name }}</p>
                            </div>

                            <!-- Deskripsi -->
                            <div class="form-group">
                                <label><strong>Deskripsi:</strong></label>
                                <p class="form-control-static">{{ $topic->description ?: 'Tidak ada deskripsi' }}</p>
                            </div>

                            <!-- Total Soal -->
                            <div class="form-group">
                                <label><strong>Total Soal:</strong></label>
                                <p class="form-control-static">
                                    <span class="badge badge-primary">{{ $allQuestions->count() }} soal</span>
                                </p>
                            </div>

                            <!-- Breakdown Tipe Soal -->
                            <div class="form-group">
                                <label><strong>Breakdown Berdasarkan Tipe Soal:</strong></label>
                                <div class="row">
                                    @php
                                        $questionTypes = $allQuestions->groupBy('question_type');
                                        $typeLabels = [
                                            'multiple_choice_single' => 'Pilihan Ganda (Tunggal)',
                                            'multiple_choice_multiple' => 'Pilihan Ganda (Multiple)',
                                            'true_false' => 'Benar/Salah',
                                            'drag_and_drop' => 'Drag & Drop'
                                        ];
                                    @endphp
                                    @foreach($typeLabels as $type => $label)
                                        <div class="col-md-6 mb-2">
                                            <div class="card bg-light">
                                                <div class="card-body py-2">
                                                    <small><strong>{{ $label }}:</strong></small>
                                                    <span class="badge badge-secondary float-right">
                                                        {{ $questionTypes->get($type, collect())->count() }} soal
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Ketersediaan Kursus -->
                            <div class="form-group">
                                <label><strong>Tersedia di Kursus:</strong></label>
                                @if($topic->available_for_all_courses)
                                    <p class="form-control-static">
                                        <span class="badge badge-success">
                                            <i class="fa fa-globe"></i> Semua Kursus Saya
                                        </span>
                                    </p>
                                @else
                                    <div class="form-control-static">
                                        @if($topic->courses && $topic->courses->count() > 0)
                                            @foreach($topic->courses as $course)
                                                <span class="badge badge-info mr-1 mb-1">{{ $course->title }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Tidak tersedia di kursus manapun</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let availableTopics = @json($availableTopics ?? []);
        let currentQuestionId = null;
        let currentTopicId = {{ $topic->id }};

        function openMoveModal(questionId, questionText) {
            currentQuestionId = questionId;
            document.getElementById('questionPreview').textContent = questionText;
            // Fix the form action URL to match your route structure
            document.getElementById('moveQuestionForm').action = `/questions/${questionId}/move`;
            
            // Reset form
            document.getElementById('topic_filter').value = 'all';
            document.getElementById('selectedTopicId').value = '';
            document.getElementById('moveButton').disabled = true;
            
            // Load topics
            filterTopics();
            
            $('#moveQuestionModal').modal('show');
        }

        function filterTopics() {
            const filter = document.getElementById('topic_filter').value;
            const topicsList = document.getElementById('topicsList');
            
            let filteredTopics = availableTopics.filter(topic => topic.id !== currentTopicId);

            if (filter === 'global') {
                filteredTopics = filteredTopics.filter(topic => topic.available_for_all_courses);
            } else if (filter !== 'all' && !isNaN(filter)) {
                filteredTopics = filteredTopics.filter(topic => 
                    topic.available_for_all_courses || 
                    (topic.courses && topic.courses.some(course => course.id == filter))
                );
            }

            topicsList.innerHTML = '';
            
            if (filteredTopics.length === 0) {
                topicsList.innerHTML = '<p class="text-muted">Tidak ada topik yang tersedia untuk filter ini.</p>';
                return;
            }

            filteredTopics.forEach(topic => {
                const topicItem = document.createElement('div');
                topicItem.className = 'topic-item p-2 mb-2 border rounded';
                topicItem.style.cursor = 'pointer';
                topicItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${topic.name}</strong>
                            <p class="mb-0 text-muted small">${topic.description || 'Tidak ada deskripsi'}</p>
                            <small class="text-info">${topic.questions_count} pertanyaan • ${topic.available_for_all_courses ? 'Semua Kursus' : 'Kursus Spesifik'}</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input topic-radio" type="radio" name="topic_selection" value="${topic.id}" id="topic_${topic.id}">
                        </div>
                    </div>
                `;
                
                topicItem.onclick = function() {
                    selectTopic(topic.id, topicItem);
                };
                
                topicsList.appendChild(topicItem);
            });
        }

        function selectTopic(topicId, element) {
            // Remove previous selection
            document.querySelectorAll('.topic-item').forEach(item => {
                item.classList.remove('border-primary', 'bg-light');
            });
            document.querySelectorAll('.topic-radio').forEach(radio => {
                radio.checked = false;
            });
            
            // Add selection to current item
            element.classList.add('border-primary', 'bg-light');
            element.querySelector('.topic-radio').checked = true;
            
            // Update hidden input and enable button
            document.getElementById('selectedTopicId').value = topicId;
            document.getElementById('moveButton').disabled = false;
        }

        function validateForm() {
            const targetTopicId = document.getElementById('selectedTopicId').value;
            const formAction = document.getElementById('moveQuestionForm').action;
            
            console.log('Form Action:', formAction);
            console.log('Target Topic ID:', targetTopicId);
            console.log('Current Question ID:', currentQuestionId);
            
            if (!targetTopicId) {
                alert('Please select a target topic before moving the question.');
                return false;
            }
            
            return true;
        }

        function closeMoveModal() {
            // Reset form dan variabel
            currentQuestionId = null;
            document.getElementById('questionPreview').textContent = '';
            document.getElementById('topic_filter').value = 'all';
            document.getElementById('selectedTopicId').value = '';
            document.getElementById('moveButton').disabled = true;
            document.getElementById('topicsList').innerHTML = '';
            
            // Tutup modal secara manual jika data-dismiss tidak bekerja
            $('#moveQuestionModal').modal('hide');
        }

        // Tambahkan event listener untuk ESC key
        $(document).ready(function() {
            $('#moveQuestionModal').on('hidden.bs.modal', function () {
                closeMoveModal();
            });
            
            // Handle ESC key
            $(document).keydown(function(e) {
                if (e.keyCode === 27 && $('#moveQuestionModal').hasClass('show')) { // ESC key
                    closeMoveModal();
                }
            });
        });
    </script>
@endsection