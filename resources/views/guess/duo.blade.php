@extends('layouts.app')

@section('content')
<div x-data="guessDuo()" x-init="init()" class="max-w-3xl mx-auto p-4">

  <!-- ================= RULES + TEAM SETUP ================= -->
  <div x-show="showRules" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold mb-2">Tebak Gambar – Duo</h3>

      <p class="text-sm mb-4">
        Setiap gambar dimainkan oleh <b>Tim A</b> lalu <b>Tim B</b>.
        Jika salah satu benar, poin bertambah dan lanjut ke gambar berikutnya.
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

  <!-- ================= SUMMARY ================= -->
  <div x-show="showSummary" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded w-80 text-center">
      <h3 class="text-xl font-bold mb-3">🏁 Game Selesai</h3>

      <p class="mb-1"><b x-text="teamNames.A"></b>: <span x-text="score.A"></span></p>
      <p class="mb-4"><b x-text="teamNames.B"></b>: <span x-text="score.B"></span></p>

      <a href="{{ route('guess.menu') }}"
         class="block w-full py-2 bg-indigo-600 text-white rounded">
        Kembali
      </a>
    </div>
  </div>

  <!-- ================= GAME AREA ================= -->
  <div class="bg-white p-4 sm:p-6 rounded shadow">

    <!-- ===== HEADER / PROGRESS ===== -->
    <div class="relative mb-4">

      <!-- TEAM NAMES -->
      <div class="absolute top-0 left-0 text-sm font-bold text-blue-600"
           x-text="teamNames.A"></div>
      <div class="absolute top-0 right-0 text-sm font-bold text-red-600"
           x-text="teamNames.B"></div>

      <!-- PROGRESS BAR -->
      <div class="mt-6 h-3 bg-gray-200 rounded relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 bg-blue-600"
             :style="`width:${bluePercent}%`"></div>
        <div class="absolute right-0 top-0 bottom-0 bg-red-600"
             :style="`width:${redPercent}%`"></div>
      </div>

      <!-- TURN + TIMER -->
      <div class="mt-2 text-center text-sm font-semibold">
        Giliran:
        <span :class="currentTurn === 'A' ? 'text-blue-600' : 'text-red-600'"
              x-text="teamNames[currentTurn]"></span>
        —
        ⏱ <span x-text="timeLeft"></span>s
      </div>
    </div>

    <!-- ===== IMAGE ===== -->
    <div class="flex justify-center mb-4">
      <img
        :src="current.image_path
          ? '{{ asset('') }}' + current.image_path
          : '{{ asset('images/placeholder.png') }}'"
        class="max-h-[280px] object-contain rounded shadow"
      >
    </div>

    <!-- PROMPT -->
    <div class="text-center mb-4" x-text="current.prompt || ''"></div>

    <!-- INPUT -->
    <div class="flex justify-center gap-1 mb-4">
      <template x-for="(slot,i) in slots" :key="i">
        <input maxlength="1"
               x-model="slots[i]"
               @input="onCharInput(i)"
               class="w-10 h-10 text-center uppercase font-bold border rounded"
               :class="inputClass">
      </template>
    </div>

    <div class="text-center">
      <button @click="submit()"
              :disabled="slots.some(s => !s)"
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

    teamNames: { A: 'Tim A', B: 'Tim B' },
    score: { A: 0, B: 0 },

    currentTurn: 'A',
    usedTurn: { A: false, B: false },

    timeLeft: 0,
    timerId: null,

    slots: [],
    showRules: true,
    showSummary: false,
    inputClass: '',

    init(){
      this.current = this.questions[this.index];
    },

    start(){
      this.showRules = false;
      this.loadQuestion();
      this.startTurn('A');
    },

    loadQuestion(){
      this.current = this.questions[this.index];
      if(!this.current){
        this.showSummary = true;
        return;
      }

      const len = this.current.answer_slots ?? 0;
      this.slots = Array.from({length: len}).map(()=>'');
      this.usedTurn = { A:false, B:false };
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
        if(this.timeLeft <= 0){
          clearInterval(this.timerId);
          this.onTimeout();
        }
      },1000);
    },

    onTimeout(){
      this.usedTurn[this.currentTurn] = true;

      if(this.currentTurn === 'A'){
        this.startTurn('B');
      } else {
        this.nextQuestion();
      }
    },

    submit(){
      clearInterval(this.timerId);

      const answer = this.slots.join('').trim();

      fetch('/guess/duo/answer',{
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
          question_id: this.current.id,
          answer: answer,
          player: this.currentTurn
        })
      })
      .then(r=>r.json())
      .then(data=>{
        if(data.correct){
          this.score[this.currentTurn]++;
          this.nextQuestion();
        } else {
          this.usedTurn[this.currentTurn] = true;
          if(this.currentTurn === 'A'){
            this.startTurn('B');
          } else {
            this.nextQuestion();
          }
        }
      });
    },

    nextQuestion(){
      this.index++;
      this.loadQuestion();
      if(!this.showSummary){
        this.startTurn('A');
      }
    },

    onCharInput(i){
      const el = event.target;
      if(el.value && i < this.slots.length-1){
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
@endsection
