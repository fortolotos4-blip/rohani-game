@extends('layouts.app')

@section('content')
<div x-data="guessSingle()" x-init="init()">

  <!-- ================= GAME AREA (DI-BLUR) ================= -->
  <div
    :class="showSummary ? 'pointer-events-none blur-sm' : ''"
    class="max-w-3xl mx-auto p-4 overflow-x-hidden transition"
  >

    <!-- TOAST -->
    <div
      x-show="toast.show"
      x-transition
      class="fixed top-5 right-5 px-4 py-2 rounded shadow-lg text-white text-sm z-50"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
      x-text="toast.message">
    </div>

    <!-- Rules modal -->
    <div x-show="showRules" class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div class="bg-white p-6 rounded w-96">
        <h3 class="text-xl font-bold">Aturan Tebak Gambar (Adventure)</h3>
        <p class="mt-2 text-sm">Anda punya 60 detik per gambar. 
        dengan sesi game 5 menit.</p>
        <p>Selamat bermain !</p>
        <div class="text-right mt-4">
          <button @click="start()" class="px-3 py-2 bg-green-600 text-white rounded">Mulai</button>
        </div>
      </div>
    </div>

    <!-- Fail modal -->
    <div x-show="showFail" class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div class="bg-white p-6 rounded w-80">
        <h3 class="text-xl font-bold text-red-600">Gagal!</h3>
        <p class="mt-2">Waktu habis. Tekan ulang untuk mencoba kembali.</p>
        <div class="mt-4 text-right">
          <button @click="restart()" class="px-3 py-2 bg-blue-600 text-white rounded">Ulang</button>
        </div>
      </div>
    </div>

    <!-- ================= MAIN UI ================= -->
    <div class="bg-white p-4 sm:p-6 rounded shadow max-w-xl mx-auto">

      <!-- HEADER TIMER -->
      <div class="flex justify-between items-center mb-4 text-sm">
        <div>
          ⏱️ Soal:
          <b :class="timeLeft <= 5 ? 'text-red-600' : ''"
             x-text="timeLeft"></b>s
        </div>
        <div>
          ⏳ Sesi:
          <b x-text="sessionTimeLeft"></b>s
        </div>
      </div>

      <div class="flex justify-center mb-4" x-show="current">
        <img 
          :src="current.image_path 
            ? '{{ asset('') }}' + current.image_path 
            : '{{ asset('images/placeholder.png') }}'"
          class="
            w-full
            max-w-[420px]
            max-h-[240px]
            sm:max-h-[300px]
            object-contain
            mx-auto
            rounded-lg
            shadow
          "
        />
      </div>

      <div class="mb-4 text-center">
        <div class="text-lg" x-text="current ? current.prompt || '' : ''"></div>
      </div>

      <!-- INPUT SLOTS -->
      <div class="w-full flex justify-center">
        <div class="flex items-center justify-center" :style="slotContainerStyle">
          <template x-for="(slot, i) in slots" :key="i">
            <input
              type="text"
              maxlength="1"
              x-model="slots[i]"
              :disabled="lockedSlots.includes(i)"
              @input="onCharInput(i)"
              class="text-center font-bold uppercase border rounded mx-[2px]"
              :class="getSlotClass(i)"
              :style="slotStyle"
            />
          </template>
        </div>
      </div>

      <div class="mt-4 flex justify-center gap-3">
        <button 
          @click="submit()" 
          :disabled="slots.some(s => !s)"
          class="px-4 py-2 bg-green-600 text-white rounded disabled:opacity-50"
        >
          Submit
        </button>

        <button
          @click="skip()"
          class="ml-3 px-4 py-2 bg-gray-400 text-white rounded">
          Skip
        </button>
      </div>

    </div>
    <!-- ================= END MAIN UI ================= -->

  </div>
  <!-- ================= END GAME AREA ================= -->

  <!-- ================= SUMMARY MODAL (TIDAK BLUR) ================= -->
  <div
    x-show="showSummary"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
  >
    <div
      class="bg-white w-[90%] max-w-sm rounded-xl shadow-xl p-6 text-center"
      @click.outside.prevent
    >
      <div class="text-4xl mb-3">🏁</div>

      <h2 class="text-xl font-bold text-gray-800 mb-4">
        Game Selesai
      </h2>

      <div class="text-sm text-gray-700 space-y-1 mb-6">
        <p>Total soal: <b x-text="summary.total"></b></p>
        <p>Jawaban benar: <b x-text="summary.correct"></b></p>
        <p>Jawaban salah: <b x-text="summary.wrong"></b></p>
      </div>

      <a
        href="{{ route('dashboard') }}"
        class="block w-full py-3 rounded-lg bg-indigo-600 text-white font-semibold"
      >
        Kembali ke Dashboard
      </a>
    </div>
  </div>

</div>

<script>
function guessSingle(){
  return {
    wrongCount: 0,        // jumlah salah di soal ini
    answeredCorrectIds: [], // id soal yang sudah benar
    lockedSlots: [],   // index slot yang dikunci (hint)
    sessionTimeLeft: 300,   // 5 menit (300 detik)
    availableQuestions: [], // soal yang belum dijawab benar

    sessionTimerId: null,
    summary: { correct: 0, wrong: 0, total: 0 }, // ✅ FIX
    questions: @json($questions ?? []),
    index: 0,
    current: null,
    timeLeft: 17,
    timerId: null,
    showRules: true,
    showFail: false,
    showSummary: false,
    slots: [],
    attempts: [], // {correct:bool}
    highlightClass: '',
    shakeInputs: false,
    shakeType: null, // 'error' | 'success'
    isSubmitting: false,


    toast: {
  show: false,
  message: '',
  type: 'success' // success | error
},

    init(){
  this.availableQuestions = [...this.questions];
},

pickRandomQuestion(){
  // 🔥 Jika semua soal sudah benar → selesai
  if(this.availableQuestions.length === 0){
    this.finishSession();
    return;
  }

  const randomIndex = Math.floor(
    Math.random() * this.availableQuestions.length
  );

  this.current = this.availableQuestions[randomIndex];
  this.wrongCount = 0;
  this.lockedSlots = [];
  this.highlightClass = '';

  const totalSlots = this.current.answer_slots ?? 0;
  this.slots = Array.from({ length: totalSlots }).map(() => '');
  this.timeLeft = this.current.time_limit_seconds ?? 16;
},

getSlotClass(i){
  if(this.lockedSlots.includes(i)){
    return 'bg-gray-200 border-gray-400 text-gray-700';
  }

  if(this.shakeInputs && this.shakeType === 'error'){
    return 'border-red-500 bg-red-50';
  }

  if(this.shakeInputs && this.shakeType === 'success'){
    return 'border-green-500 bg-green-50';
  }

  return 'border-gray-300 bg-white';
},

    
    start(){
  this.showRules = false;

  this.sessionTimeLeft = 300;
  this.startSessionTimer();

  this.attempts = [];
  this.answeredCorrectIds = [];
  this.availableQuestions = [...this.questions];

  this.pickRandomQuestion();
  this.startTimer();
},

showToast(message, type = 'success'){
  this.toast.message = message;
  this.toast.type = type;
  this.toast.show = true;

  setTimeout(() => {
    this.toast.show = false;
  }, 1200);
},

startSessionTimer(){
  if(this.sessionTimerId) clearInterval(this.sessionTimerId);

  this.sessionTimerId = setInterval(() => {
    this.sessionTimeLeft--;

    if(this.sessionTimeLeft <= 0){
      clearInterval(this.sessionTimerId);
      clearInterval(this.timerId);
      this.finishSession();
    }
  }, 1000);
},

    loadCurrent(){
  this.highlightClass = '';
  this.wrongCount = 0;
  this.lockedSlots = []; // 🔥 reset hint

  this.current = this.questions[this.index];
  if(!this.current) return;

  const totalSlots = this.current.answer_slots ?? 0;
  this.slots = Array.from({ length: totalSlots }).map(() => '');
  this.timeLeft = this.current.time_limit_seconds ?? 16;
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
    onCharInput(i){
      // auto focus next
      const el = event.target;
      if(el.value && i < this.slots.length-1){
        el.nextElementSibling?.focus();
      }
    },
    submit(){
  if(!this.current) return;
  if(this.isSubmitting) return;

  // ⛔ LOCK SEMUA AKSI
  this.isSubmitting = true;
  clearInterval(this.timerId);

  const answer = this.slots.join('').trim();

  const payload = { 
    question_id: this.current.id, 
    answer: answer, 
    time_taken_seconds: (this.current.time_limit_seconds ?? 16) - this.timeLeft 
  };

  fetch("/guess/single/answer", {
    method:'POST',
    headers:{
      'Content-Type':'application/json',
      'X-CSRF-TOKEN':'{{ csrf_token() }}'
    },
    body: JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(data => {
    const correct = !!data.correct;

    if(correct){
      this.shakeInputs = true;
      this.shakeType = 'success';

      this.attempts.push({correct:true});
      this.answeredCorrectIds.push(this.current.id);

      // hapus dari pool
      this.availableQuestions = this.availableQuestions.filter(
        q => q.id !== this.current.id
      );

      setTimeout(() => {
        this.shakeInputs = false;
        this.shakeType = null;
        this.isSubmitting = false;

        this.pickRandomQuestion();
        this.startTimer();
      }, 700);

    } else {
      // ❌ SALAH
      this.shakeInputs = true;
      this.shakeType = 'error';

      this.attempts.push({correct:false});
      this.wrongCount++;

      // hint
      if(this.wrongCount === 5){
        const firstChar = this.current.answer_text
          .replace(/\s+/g,'')
          .charAt(0)
          .toUpperCase();

        this.slots[0] = firstChar;
        this.lockedSlots = [0];
      }

      setTimeout(() => {
        this.slots = this.slots.map((v,i) =>
          this.lockedSlots.includes(i) ? v : ''
        );

        this.shakeInputs = false;
        this.shakeType = null;
        this.isSubmitting = false;

        this.startTimer();
      }, 500);
    }
  })
  .catch(() => {
    this.isSubmitting = false;
    this.startTimer();
  });
},

skip(){
  if(this.isSubmitting) return;

  clearInterval(this.timerId);
  this.pickRandomQuestion();
  this.startTimer();
},

get slotStyle(){
  const count = this.slots.length;

  // ukuran default
  let size = 42;

  if (count >= 8)  size = 36;
  if (count >= 10) size = 32;
  if (count >= 12) size = 28;
  if (count >= 15) size = 24;

  return `
    width:${size}px;
    height:${size}px;
    min-width:${size}px;
    min-height:${size}px;
    font-size:${Math.max(14, size * 0.45)}px;
  `;
},

get slotContainerStyle(){
  const count = this.slots.length;
  const gap = 4; // total horizontal margin (mx-2px)
  const slotSize = this.slotStyle.match(/width:(\d+)/)[1];

  const totalWidth = count * slotSize + count * gap * 2;
  const maxWidth = Math.min(window.innerWidth - 32, 420);

  if (totalWidth > maxWidth) {
    const scale = maxWidth / totalWidth;
    return `
      transform: scale(${scale});
      transform-origin: center;
    `;
  }

  return '';
},

    onTimeout(){
  if(this.isSubmitting) return;
  if(this.sessionTimeLeft <= 0) return;

  this.attempts.push({correct:false});
  this.pickRandomQuestion();
  this.startTimer();
},

    restart(){
      this.showFail = false;
      this.loadCurrent();
      this.startTimer();
    },
    nextOrFinish(){
      // small delay to allow user see result
      setTimeout(()=>{
        this.index++;
        if(this.index >= this.questions.length){
          this.computeSummary();
          this.showSummary = true;
        } else {
          this.loadCurrent();
          this.startTimer();
        }
      },700);
    },

    computeSummary(){
  const totalPlayed = this.attempts.length;
  const correct = this.attempts.filter(a => a.correct).length;

  this.summary = {
    correct: correct,
    wrong: totalPlayed - correct,
    total: totalPlayed
  };
},

    finishSession(){
  clearInterval(this.timerId);
  clearInterval(this.sessionTimerId);

  this.computeSummary();
  this.showSummary = true;
},

  }
}
</script>
@endsection

<style>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

.shake {
  animation: shake 0.25s ease-in-out;
}
</style>
