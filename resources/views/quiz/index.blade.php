@extends('layouts.app')

@section('sidebar')
<a href="{{ route('dashboard') }}"
   class="block px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200">
  ← Kembali
</a>
@endsection

@section('content')
<div x-data="quizApp()" x-init="init()" class="max-w-xl mx-auto px-3 sm:px-0">

  <!-- ================= RULES MODAL ================= -->
  <div x-show="showRules" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white p-5 rounded-lg w-full max-w-sm mx-4">
      <h3 class="text-lg font-bold mb-2">Aturan Quiz</h3>
      <p class="text-sm text-gray-600">
        Kamu punya <b>16 detik</b> untuk setiap soal. Pilih satu jawaban.
      </p>
      <div class="text-right mt-4">
        <button @click="start()"
          class="px-4 py-2 bg-green-600 text-white rounded">
          Mulai
        </button>
      </div>
    </div>
  </div>

  <!-- ================= FAIL MODAL ================= -->
  <div x-show="showFail" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white p-5 rounded-lg w-full max-w-sm mx-4">
      <h3 class="text-lg font-bold text-red-600">⏰ Waktu Habis</h3>
      <p class="text-sm mt-2">Coba lagi ya!</p>
      <div class="text-right mt-4">
        <button @click="restart()"
          class="px-4 py-2 bg-blue-600 text-white rounded">
          Ulang
        </button>
      </div>
    </div>
  </div>

  <!-- ================= SUMMARY MODAL ================= -->
  <div x-show="showSummary" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white p-5 rounded-lg w-full max-w-sm mx-4">
      <h3 class="text-lg font-bold mb-2">Hasil Quiz</h3>
      <p class="text-sm">✅ Benar: <b x-text="summary.correct"></b></p>
      <p class="text-sm">❌ Salah: <b x-text="summary.wrong"></b></p>
      <p class="text-sm">📊 Total: <b x-text="summary.total"></b></p>

      <div class="text-right mt-4">
        <button @click="goDashboard()"
          class="px-4 py-2 bg-indigo-600 text-white rounded">
          Dashboard
        </button>
      </div>
    </div>
  </div>

  <!-- ================= MAIN QUIZ ================= -->
  <div class="bg-white rounded-xl shadow p-4 sm:p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
      <div class="text-sm font-semibold">
        ⏱️
        <span
          x-text="timeLeft"
          :class="timeLeft <= 5 ? 'text-red-600 font-bold' : 'text-gray-800'">
        </span> dtk
      </div>
      <div class="text-xs text-gray-500">
        Soal <span x-text="currentIndex + 1"></span>/<span x-text="totalQuestions"></span>
      </div>
    </div>

    <!-- IMAGE -->
    <div class="flex justify-center mb-4">
      <img
        :src="currentQuestion?.image_url || '/images/placeholder.png'"
        class="max-h-56 sm:max-h-72 w-auto object-contain rounded-lg shadow"
      />
    </div>

    <!-- QUESTION -->
    <div class="mb-4 text-sm sm:text-base font-medium text-gray-800"
         x-text="currentQuestion?.prompt || 'Tidak ada soal'">
    </div>

    <!-- ANSWERS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <template x-for="choice in currentChoices" :key="choice.id">
        <button
          @click="submit(choice.id)"
          :class="choiceClass(choice.id)"
          class="p-3 border rounded-lg text-left text-sm hover:bg-gray-50">
          <span x-text="choice.text"></span>
        </button>
      </template>
    </div>

    <!-- FEEDBACK -->
    <div x-show="answered" class="mt-4">
      <div class="font-semibold text-sm"
           :class="correct ? 'text-green-600' : 'text-red-600'"
           x-text="feedbackText">
      </div>

      <div class="text-xs text-gray-600 mt-1" x-text="explanation"></div>

      <div class="text-right mt-3">
        <button @click="next()"
          class="px-4 py-2 bg-indigo-600 text-white rounded">
          Lanjut →
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

    // simpan percobaan lokal (bisa juga simpan di server)
    attempts: [], // tiap item: { question_id, correct, choice_id, time_taken_seconds }

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
      // timeout -> catat attempt (sebagai timeout / salah)
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

      fetch("{{ route('quiz.answer') }}", {
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
      }).then(r=>r.json()).then(data=>{
        this.answered = true;
        this.correct = !!data.correct;
        this.explanation = data.explanation ?? ('Jawaban: ' + (data.correct_answer ?? ''));
        this.feedbackText = this.correct ? 'Benar!' : 'Salah';

        // simpan attempt lokal
        this.attempts.push({
          question_id: this.currentQuestion.id,
          correct: this.correct,
          choice_id: choiceId,
          time_taken_seconds: taken
        });

      }).catch(()=> {
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
      } else {
        if(id === this.chosenId) return 'border-2 border-red-500 bg-red-50';
        return '';
      }
    },

    next(){
      // pindah ke soal berikutnya (atau tampilkan summary jika selesai)
      const nextIndex = this.currentIndex + 1;
      if(nextIndex >= this.totalQuestions){
        // selesai -> hitung summary dan tampilkan
        this.computeSummary();
        this.showSummary = true;
        return;
      }
      // else load next question
      this.loadQuestion(nextIndex);
      this.startTimer();
    },

    restart(){
      this.showFail = false;
      // ulang soal yang sama (tidak mengubah urutan)
      this.startTimer();
    },

    computeSummary(){
      const total = this.attempts.length;
      let correct = 0;
      for(const a of this.attempts) if(a.correct) correct++;
      const wrong = total - correct;
      this.summary = { correct: correct, wrong: wrong, total: total };
      // Anda bisa juga kirim attempts ke server untuk disimpan di DB jika mau
      // fetch('/game/save-attempts', { method:'POST', body: JSON.stringify(this.attempts) ... })
    },

    goDashboard(){
      window.location.href = "{{ route('dashboard') }}";
    }
  }
}
</script>
@endsection
