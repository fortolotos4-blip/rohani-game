@extends('layouts.app')

@section('content')
<div x-data="guessDuo()" x-init="init()" class="max-w-3xl mx-auto p-4">

  <!-- RULES -->
  <div x-show="showRules" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold mb-3">Tebak Gambar – Duo</h3>
      <p class="text-sm mb-4">
        Jika jawaban benar, poin bertambah dan giliran soal berikutnya dimulai oleh tim pemenang.
      </p>

      <div class="space-y-3">
        <div>
          <label class="text-sm font-semibold">Nama Tim A</label>
          <input x-model="teamNames.A" class="w-full border rounded px-2 py-1">
        </div>
        <div>
          <label class="text-sm font-semibold">Nama Tim B</label>
          <input x-model="teamNames.B" class="w-full border rounded px-2 py-1">
        </div>
      </div>

      <div class="text-right mt-4">
        <button @click="start()" class="px-4 py-2 bg-green-600 text-white rounded">
          Mulai
        </button>
      </div>
    </div>
  </div>

  <!-- SUMMARY -->
  <div x-show="showSummary" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-80 text-center">
      <h3 class="text-xl font-bold mb-4">🏁 Game Selesai</h3>
      <p><b x-text="teamNames.A"></b>: <span x-text="score.A"></span></p>
      <p class="mb-4"><b x-text="teamNames.B"></b>: <span x-text="score.B"></span></p>

      <a href="{{ route('guess.menu') }}"
         class="block w-full py-2 bg-indigo-600 text-white rounded">
        Kembali
      </a>
    </div>
  </div>

  <!-- GAME CARD -->
  <div class="bg-white p-4 sm:p-6 rounded shadow">

    <!-- HEADER -->
    <div class="mb-4">
      <div class="flex justify-between mb-2">
        <div
          class="px-3 py-1 rounded text-sm font-bold transition"
          :class="currentTurn==='A'
            ? 'bg-blue-100 text-blue-700 ring-2 ring-blue-400'
            : 'bg-gray-200 text-blue-600'">
          <span x-text="teamNames.A"></span>
        </div>

        <div
          class="px-3 py-1 rounded text-sm font-bold transition"
          :class="currentTurn==='B'
            ? 'bg-red-100 text-red-700 ring-2 ring-red-400'
            : 'bg-gray-200 text-red-600'">
          <span x-text="teamNames.B"></span>
        </div>
      </div>

      <div class="h-4 bg-gray-200 rounded overflow-hidden relative mb-1">

  <!-- BLUE BAR -->
  <div class="absolute left-0 top-0 bottom-0 bg-blue-600 transition-all duration-500"
       :style="`width:${bluePercent}%`"></div>

  <!-- RED BAR -->
  <div class="absolute right-0 top-0 bottom-0 bg-red-600 transition-all duration-500"
       :style="`width:${redPercent}%`"></div>

  <!-- 🔥 SCORE TEXT (DI TENGAH) -->
  <div class="absolute inset-0 flex items-center justify-center
              text-xs font-bold text-white pointer-events-none">
    <span x-text="score.A"></span>
    <span class="mx-1">-</span>
    <span x-text="score.B"></span>
  </div>

</div>

      <div class="text-center text-sm font-semibold">
        Giliran:
        <span :class="currentTurn==='A' ? 'text-blue-600' : 'text-red-600'"
              x-text="teamNames[currentTurn]"></span>
        —
        <span :class="timeLeft<=5 ? 'text-red-600 font-bold animate-pulse' : ''">
          ⏱ <span x-text="timeLeft"></span>s
        </span>
      </div>
    </div>

    <!-- IMAGE -->
    <div class="flex justify-center mb-5">
      <img
        :src="current?.image_path
          ? '{{ asset('') }}' + current.image_path
          : '{{ asset('images/placeholder.png') }}'"
        class="max-h-[300px] w-full object-contain rounded-lg shadow">
    </div>

    <!-- INPUT -->
    <div class="flex justify-center gap-1 mb-4"
         :class="shake ? 'shake' : ''">
      <template x-for="(slot,i) in slots" :key="i">
        <input
          maxlength="1"
          x-model="slots[i]"
          @input="onCharInput(i)"
          :disabled="isSubmitting"
          class="w-10 h-10 text-center uppercase font-bold border rounded"
          :class="inputClass">
      </template>
    </div>

    <div class="text-center">
      <button
        @click="submit()"
        :disabled="isSubmitting || slots.some(s=>!s)"
        class="px-4 py-2 bg-green-600 text-white rounded disabled:opacity-50">
        Submit
      </button>
    </div>

  </div>
</div>

<script>
function guessDuo(){
  return {
    questions: @json($questions ?? []),
    index: 0,
    current: null,

    teamNames: { A:'Tim A', B:'Tim B' },
    score: { A:0, B:0 },

    currentTurn: 'A',
    nextStartTurn: 'A',

    timeLeft: 0,
    timerId: null,

    slots: [],
    lockedIndexes: [],
    revealedIndexes: [],
    wrongAttempts: 0,

    isSubmitting: false,
    inputClass: '',
    shake: false,

    showRules: true,
    showSummary: false,

    init(){
      this.current = this.questions[0] || null;
    },

    start(){
      this.showRules = false;
      this.loadQuestion();
      this.startTurn(this.nextStartTurn);
    },

    loadQuestion(){
      this.current = this.questions[this.index];
      if(!this.current){
        this.showSummary = true;
        return;
      }

      const len = this.current.answer_slots ?? 0;
      this.slots = Array(len).fill('');
      this.lockedIndexes = [];
      this.revealedIndexes = [];
      this.wrongAttempts = 0;
      this.inputClass = '';
    },

    startTurn(turn){
      this.currentTurn = turn;
      this.timeLeft = this.current.time_limit_seconds ?? 60;
      this.startTimer();
    },

    startTimer(){
      if(this.timerId) clearInterval(this.timerId);

      this.timerId = setInterval(()=>{
        this.timeLeft--;

        if(this.timeLeft === 30){
          this.revealHintLetter(); // 🔥 HINT Cerdas
        }

        if(this.timeLeft <= 0){
          clearInterval(this.timerId);
          this.onTimeout();
        }
      },1000);
    },

    onTimeout(){
      if(this.currentTurn === 'A'){
        this.startTurn('B');
      } else {
        this.nextStartTurn = 'A';
        this.nextQuestion();
      }
    },

    normalize(str){
      return str.toLowerCase().replace(/[^a-z0-9]/g,'');
    },

    submit(){
      if(this.isSubmitting) return;
      this.isSubmitting = true;

      const answer = this.normalize(this.slots.join(''));

      fetch('/guess/duo/answer',{
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
          question_id:this.current.id,
          answer: answer,
          player:this.currentTurn // 🔥 FIXED
        })
      })
      .then(r=>r.ok ? r.json() : Promise.reject())
      .then(data=>{
        if(data.correct){
          this.inputClass = 'bg-green-100 border-green-500';
          this.score[this.currentTurn]++;
          this.nextStartTurn = this.currentTurn;

          setTimeout(()=> this.nextQuestion(),200);
        } else {
          this.handleWrong();
        }
      })
      .catch(()=> this.handleWrong())
      .finally(()=> this.isSubmitting = false);
    },

    handleWrong(){
      this.wrongAttempts++;
      this.inputClass = 'bg-red-100 border-red-500';
      this.shake = true;

      setTimeout(()=>{
        this.shake = false;
        this.inputClass = '';
        this.slots = this.slots.map((v,i)=>
          this.lockedIndexes.includes(i) ? v : ''
        );
      },200);
    },

    revealHintLetter(){
      if(this.lockedIndexes.length) return; // 🔒 hanya 1x per soal

      const answer = this.normalize(this.current.answer_text);
      const mid = Math.floor(answer.length / 2);

      this.slots[mid] = answer[mid].toUpperCase();
      this.lockedIndexes.push(mid);
      this.revealedIndexes.push(mid);
    },

    nextQuestion(){
      this.index++;
      this.loadQuestion();
      if(!this.showSummary){
        this.startTurn(this.nextStartTurn);
      }
    },

    onCharInput(i){
      if(this.lockedIndexes.includes(i)) return;

      const el = event.target;
      if(el.value && i < this.slots.length - 1){
        el.nextElementSibling?.focus();
      }
    },

    get bluePercent(){
      const t = this.score.A + this.score.B;
      return t ? Math.round(this.score.A / t * 100) : 0;
    },

    get redPercent(){
      const t = this.score.A + this.score.B;
      return t ? Math.round(this.score.B / t * 100) : 0;
    }
  }
}
</script>


<style>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20% { transform: translateX(-6px); }
  40% { transform: translateX(6px); }
  60% { transform: translateX(-6px); }
  80% { transform: translateX(6px); }
}
.shake {
  animation: shake 0.4s ease-in-out;
}
</style>
@endsection
