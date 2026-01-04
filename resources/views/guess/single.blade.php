@extends('layouts.app')

@section('content')
<div x-data="guessSingle()" x-init="init()" class="max-w-3xl mx-auto p-4">
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
      <h3 class="text-xl font-bold">Aturan Tebak Gambar (Single)</h3>
      <p class="mt-2 text-sm">Anda punya <strong>17 detik</strong> per gambar. 
      Isi kotak huruf sesuai jawaban.</p>
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

  <!-- Summary modal -->
  <div x-show="showSummary"
     class="fixed inset-0 bg-black/50 flex items-center justify-center">
  <div class="bg-white p-6 rounded w-96 text-center space-y-3">

    <h3 class="text-xl font-bold text-green-700">
      🎉 Sesi Selesai
    </h3>

    <p class="text-sm text-gray-600">
      Terima kasih sudah bermain 🙏
    </p>

    <div class="mt-3 text-sm">
      <p>Soal dimainkan: <b x-text="summary.total"></b></p>
      <p>Jawaban benar: <b x-text="summary.correct"></b></p>
      <p>Jawaban salah: <b x-text="summary.wrong"></b></p>
    </div>

    <p class="mt-2 text-sm text-indigo-600 font-semibold">
      Semoga permainan ini menambah wawasan dan berkat ✨
    </p>

    <div class="mt-4">
      <a href="{{ route('dashboard') }}"
         class="inline-block px-4 py-2 bg-indigo-600 text-white rounded">
        Kembali ke Dashboard
      </a>
    </div>

  </div>
</div>

  <!-- Main UI -->
  <div class="bg-white p-4 sm:p-6 rounded shadow max-w-xl mx-auto">


    <!-- HEADER TIMER -->
<div class="flex justify-between items-center mb-4 text-sm">

  <!-- LEFT -->
  <div class="space-x-4">
    <span>
      ⏱️ Soal:
      <b :class="timeLeft <= 5 ? 'text-red-600' : ''"
         x-text="timeLeft"></b>s
    </span>

    <span>
      ⏳ Sesi:
      <b x-text="sessionTimeLeft"></b>s
    </span>
  </div>

  <!-- RIGHT : PROGRESS -->
  <div class="w-32 bg-gray-200 rounded h-3 relative">
    <div :style="`width:${progress}%`"
         class="absolute left-0 top-0 bottom-0 bg-green-500 rounded transition-all"></div>
    <div class="absolute inset-0 text-[10px] text-white font-bold flex items-center justify-center"
         x-text="progressText"></div>
  </div>

</div>


    <div class="flex justify-center mb-4" x-show="current">
  <img 
    :src="current.image_path 
      ? '{{ asset('') }}' + current.image_path 
      : '{{ asset('images/placeholder.png') }}'"
    class="
      max-h-[220px]
      sm:max-h-[300px]
      md:max-h-[360px]
      w-auto
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

<!-- INPUT SLOTS -->
<div
  class="flex flex-wrap justify-center gap-2 max-w-md mx-auto"
  :class="{
    'shake shake-error': shakeInputs && shakeType === 'error',
    'pulse-success': shakeInputs && shakeType === 'success'
  }"
>

  <template x-for="(slot, i) in slots" :key="i">
    <input
      type="text"
      maxlength="1"
      x-model="slots[i]"
      :disabled="lockedSlots.includes(i)"
      @input="onCharInput(i)"
      class="
        text-center border rounded font-bold uppercase
        w-8 h-8
        sm:w-9 sm:h-9
        md:w-10 md:h-10
        transition
      "
      :class="`
        ${lockedSlots.includes(i) ? 'bg-gray-200 text-gray-700' : 'bg-white'}
        ${highlightClass}
      `"
    />
  </template>
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
    progress: 0,
    progressText: '0%',
    slots: [],
    attempts: [], // {correct:bool}
    highlightClass: '',
    shakeInputs: false,
    shakeType: null, // 'error' | 'success'


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
  clearInterval(this.timerId);
  const answer = this.slots.join('').trim();
  const payload = { 
      question_id: this.current.id, 
      answer: answer, 
      time_taken_seconds: (this.current.time_limit_seconds ?? 16) - this.timeLeft 
  };

  fetch("/guess/single/answer", {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body: JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(data => {
  const correct = !!data.correct;

  if(correct){
  this.shakeInputs = true;
  this.shakeType = 'success';
  this.showToast('Jawaban benar 🎉','success');
  this.highlightClass = 'border-2 border-green-500 bg-green-50';

  this.attempts.push({correct:true});
  this.answeredCorrectIds.push(this.current.id);

  // 🔥 HAPUS soal ini dari pool
  this.availableQuestions = this.availableQuestions.filter(
    q => q.id !== this.current.id
  );

  this.updateProgress();

  setTimeout(() => {
  this.shakeInputs = false;
  this.shakeType = null;

  this.pickRandomQuestion();
  this.startTimer();
  }, 700);

  return;
} else {
  // ❌ SALAH
  // ❌ SALAH
this.shakeInputs = true;
this.shakeType = 'error';

this.highlightClass = 'border-2 border-red-500 bg-red-50';
this.showToast('Jawaban salah 😅','error');

this.attempts.push({correct:false});
this.wrongCount++;


  // 🔓 Salah ke-5 → buka hint
  if(this.wrongCount === 5){
    const firstChar = this.current.answer_text
      .replace(/\s+/g,'')
      .charAt(0)
      .toUpperCase();

    this.slots[0] = firstChar;
    this.lockedSlots = [0];
  }

  setTimeout(() => {
    // 🔥 kosongkan slot KECUALI yang terkunci
    this.slots = this.slots.map((v,i) =>
      this.lockedSlots.includes(i) ? v : ''
    );
    this.highlightClass = '';
    this.shakeInputs = false;
    this.shakeType = null;
  }, 500);

  this.startTimer();
}
})

  .catch(()=>{
      // treat as wrong
      this.attempts.push({correct:false});
      this.progress = Math.round( (this.attempts.filter(a=>a.correct).length / this.questions.length) * 100 );
      this.progressText = this.progress + '%';
      this.highlightClass = 'border-2 border-red-500 bg-red-50';
      this.nextOrFinish();
  });
},
skip(){
  clearInterval(this.timerId);

  if(this.availableQuestions.length <= 1){
    // hanya tersisa 1 soal → lanjutkan saja
    this.startTimer();
    return;
  }

  this.pickRandomQuestion();
  this.startTimer();
},

    onTimeout(){
  if(this.sessionTimeLeft <= 0) return;

  this.attempts.push({correct:false});
  this.pickRandomQuestion();
  this.startTimer();
},

updateProgress(){
  const correct = this.attempts.filter(a => a.correct).length;
  this.progress = Math.round((correct / this.questions.length) * 100);
  this.progressText = this.progress + '%';
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
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.shake {
  animation: shake 0.3s ease-in-out;
}

/* ❌ SALAH */
.shake-error {
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.6); /* red */
}

/* ✅ BENAR */
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(34,197,94,.7); }
  70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
  100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
}

.pulse-success {
  animation: pulse 0.6s ease-out;
}

</style>
