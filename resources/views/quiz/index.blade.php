@extends('layouts.app')


@section('content')
<div x-data="quizApp()" x-init="init()" class="max-w-3xl mx-auto">

  <!-- Aturan modal -->
  <div x-show="showRules" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Aturan Quiz</h3>
      <p class="mt-2 text-sm">
        Anda punya 16 detik setiap soal. Pilih satu jawaban A–D.
        Jika waktu habis, muncul popup gagal.
      </p>
      <div class="text-right mt-4">
        <button @click="start()" class="px-3 py-2 bg-green-600 text-white rounded">
          Mulai
        </button>
      </div>
    </div>
  </div>

  <!-- Gagal modal -->
  <div x-show="showFail" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded">
      <h3 class="text-xl font-bold text-red-600">Gagal!</h3>
      <p class="mt-2">Waktu habis. Coba lagi.</p>
      <div class="mt-4 text-right">
        <button @click="restart()" class="px-3 py-2 bg-blue-600 text-white rounded">
          Ulang
        </button>
      </div>
    </div>
  </div>

  <!-- Summary modal -->
  <div x-show="showSummary" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Ringkasan Permainan</h3>
      <p class="mt-2">Benar: <strong x-text="summary.correct"></strong></p>
      <p class="mt-1">Salah: <strong x-text="summary.wrong"></strong></p>
      <p class="mt-1">Total: <strong x-text="summary.total"></strong></p>
      <div class="mt-4 text-right">
        <button @click="goDashboard()" class="px-3 py-2 bg-indigo-600 text-white rounded">
          Kembali ke Dashboard
        </button>
      </div>
    </div>
  </div>

  <!-- Main -->
  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
      <div class="text-sm font-semibold">
        Waktu:
        <span
          x-text="timeLeft"
          :class="timeLeft <= 5 ? 'text-red-600 font-bold' : 'text-black'"
        ></span>
        detik
      </div>
      <div class="text-sm text-gray-500">
        Soal ke <span x-text="currentIndex + 1"></span> /
        <span x-text="totalQuestions"></span>
      </div>
    </div>

    <div class="flex justify-center mb-4">
      <img 
  :src="currentQuestion && currentQuestion.image_url 
    ? currentQuestion.image_url 
    : '/images/placeholder.png'"
  class="max-h-72 w-auto object-contain rounded-lg shadow-md"
/>
    </div>

    <div class="mb-4">
      <div x-text="currentQuestion ? currentQuestion.prompt : 'Tidak ada soal'"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <template x-for="choice in currentChoices" :key="choice.id">
        <button
          @click="submit(choice.id)"
          :class="choiceClass(choice.id)"
          class="p-3 text-left border rounded"
        >
          <span x-text="choice.text"></span>
        </button>
      </template>
    </div>

    <div x-show="answered" class="mt-4">
      <div x-text="feedbackText" class="font-semibold"></div>
      <div class="mt-2 text-sm text-gray-600" x-text="explanation"></div>
      <div class="mt-3 text-right">
        <button @click="next()" class="px-3 py-2 bg-indigo-600 text-white rounded">
          Soal Berikutnya
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function quizApp(){
  return {
    // ambil array soal dari server yang dikirim di blade
    questions: @json($questions ?? []),

    // state permainan
    currentIndex: 0,
    totalQuestions: 0,
    currentQuestion: null,
    currentChoices: [],
    timeLeft: 16,
    timerId: null,
    answered: false,
    correct: false,
    chosenId: null,
    explanation: '',
    feedbackText: '',
    showRules: true,
    showFail: false,
    showSummary: false,

    // simpan percobaan lokal
    attempts: [],

    summary: { correct:0, wrong:0, total:0 },

    init(){
      this.totalQuestions = this.questions.length;
      if(this.totalQuestions > 0){
        this.loadQuestion(0);
      }
    },

    start(){
      if(this.totalQuestions === 0){
        alert('Belum ada soal di database.');
        return;
      }
      this.showRules = false;
      this.startTimer();
    },

    loadQuestion(index){
      this.currentIndex = index;
      this.currentQuestion = this.questions[index] || null;
      this.currentChoices = this.currentQuestion ? this.currentQuestion.choices : [];
    },

    startTimer(){
      this.timeLeft = this.currentQuestion.time_limit_seconds ?? 16;
      this.answered = false;
      this.chosenId = null;
      this.correct = false;
      this.explanation = '';
      this.feedbackText = '';

      if(this.timerId) clearInterval(this.timerId);

      this.timerId = setInterval(()=>{
        this.timeLeft--;
        if(this.timeLeft <= 0){
          clearInterval(this.timerId);
          this.timeUp();
        }
      },1000);
    },

    timeUp(){
      this.attempts.push({
        question_id: this.currentQuestion.id,
        correct: false,
        choice_id: null,
        time_taken_seconds: this.currentQuestion.time_limit_seconds ?? 16
      });
      this.showFail = true;
    },

    submit(choiceId){
      if(this.answered) return;

      clearInterval(this.timerId);
      const taken = (this.currentQuestion.time_limit_seconds ?? 16) - this.timeLeft;
      this.chosenId = choiceId;

      fetch("/quiz/answer", {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
          question_id: this.currentQuestion.id,
          choice_id: choiceId,
          time_taken_seconds: taken
        })
      })
      .then(r=>r.json())
      .then(data=>{
        this.answered = true;
        this.correct = !!data.correct;
        this.explanation = data.explanation ?? ('Jawaban: ' + (data.correct_answer ?? ''));
        this.feedbackText = this.correct ? 'Benar!' : 'Salah';

        this.attempts.push({
          question_id: this.currentQuestion.id,
          correct: this.correct,
          choice_id: choiceId,
          time_taken_seconds: taken
        });
      })
      .catch(()=>{
        this.answered = true;
        this.correct = false;
        this.feedbackText = 'Error pada server';

        this.attempts.push({
          question_id: this.currentQuestion.id,
          correct: false,
          choice_id: choiceId,
          time_taken_seconds: taken
        });
      });
    },

    choiceClass(id){
      if(!this.answered) return '';
      if(this.correct){
        return id === this.chosenId ? 'border-2 border-green-500 bg-green-50' : '';
      }else{
        if(id === this.chosenId) return 'border-2 border-red-500 bg-red-50';
        return '';
      }
    },

    next(){
      const nextIndex = this.currentIndex + 1;
      if(nextIndex >= this.totalQuestions){
        this.computeSummary();
        this.showSummary = true;
        return;
      }
      this.loadQuestion(nextIndex);
      this.startTimer();
    },

    restart(){
      this.showFail = false;
      this.startTimer();
    },

    computeSummary(){
      const total = this.attempts.length;
      let correct = 0;
      for(const a of this.attempts) if(a.correct) correct++;
      const wrong = total - correct;
      this.summary = { correct, wrong, total };
    },

    goDashboard(){
      window.location.href = "{{ route('dashboard') }}";
    }
  }
}
</script>
@endsection
