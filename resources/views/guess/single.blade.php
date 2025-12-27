@extends('layouts.app')

@section('sidebar')
  <a href="{{ route('guess.menu') }}" class="block py-2">Back</a>
@endsection

@section('content')
<div x-data="guessSingle()" x-init="init()" class="max-w-3xl mx-auto p-4">
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
  <div x-show="showSummary" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Ringkasan</h3>
      <p class="mt-2">Benar: <strong x-text="summary.correct"></strong></p>
      <p class="mt-1">Salah: <strong x-text="summary.wrong"></strong></p>
      <div class="mt-4 text-right">
        <a href="{{ route('guess.menu') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Kembali</a>
      </div>
    </div>
  </div>

  <!-- Main UI -->
  <div class="bg-white p-6 rounded shadow">
    <div class="flex items-center justify-between mb-4">
      <div class="text-sm font-semibold">Waktu: <span 
        x-text="timeLeft"
        :class="timeLeft <= 5 ? 'text-red-600 font-bold' : 'text-black'"
    ></span> detik</div>
      <div class="w-1/3 bg-gray-200 rounded h-4 relative">
        <div :style="`width:${progress}%`" class="absolute left-0 top-0 bottom-0 bg-green-500 rounded transition-all duration-500"></div>
        <div class="absolute inset-0 flex items-center justify-center text-xs text-white font-bold" x-text="progressText"></div>
      </div>
    </div>

    <div class="flex justify-center mb-4">
      <img 
    :src="current.image_path ? '/storage/' + current.image_path : '/images/placeholder.png'" 
    class="max-h-[420px] w-auto object-contain mx-auto rounded-lg shadow"
>
    </div>

    <div class="mb-4 text-center">
      <div class="text-lg" x-text="current ? current.prompt || '' : ''"></div>
    </div>

    <div class="flex justify-center gap-2">
      <template x-for="(slot, i) in slots" :key="i">
        <input type="text"
       maxlength="1"
       x-model="slots[i]"
       @input="onCharInput(i)"
       :class="'w-10 h-10 text-center border rounded ' + highlightClass"
/>
      </template>
    </div>

    <div class="mt-4 text-center">
      <button @click="submit()" class="px-4 py-2 bg-green-600 text-white rounded">Submit</button>
    </div>
  </div>
</div>

<script>
function guessSingle(){
  return {
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
    init(){
      if(this.questions.length) {
        this.current = this.questions[0];
        this.total = this.questions.length;
      }
    },
    start(){
  this.showRules = false;
  // reset progress & attempts saat mulai
  this.progress = 0;
  this.progressText = '0%';
  this.attempts = [];
  this.index = 0;         // optional: mulai dari soal pertama
  this.loadCurrent();
  this.startTimer();
},

    loadCurrent(){
      this.highlightClass = '';
      this.current = this.questions[this.index];
      // prepare input slots based on answer length (strip spaces)
      const ans = (this.current.answer_text || '').replace(/\s+/g,'');
      this.slots = Array.from({length: ans.length}).map(()=>'');
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

  fetch("{{ route('guess.single.answer') }}", {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
    body: JSON.stringify(payload)
  })
  .then(r=>r.json())
  .then(data=>{
      const correct = !!data.correct;
      this.attempts.push({correct});

      // ⭐ update progress setiap submit (benar / salah)
      this.progress = Math.round( (this.attempts.filter(a=>a.correct).length / this.questions.length) * 100 );
      this.progressText = this.progress + '%';

      // ⭐ highlight box
      this.highlightClass = correct 
          ? 'border-2 border-green-500 bg-green-50' 
          : 'border-2 border-red-500 bg-red-50';

      this.nextOrFinish();
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
    onTimeout(){
      this.attempts.push({correct:false});
      this.showFail = true;
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
      const total = this.attempts.length;
      const correct = this.attempts.filter(a=>a.correct).length;
      this.summary = {correct: correct, wrong: total - correct, total: total};
    }
  }
}
</script>
@endsection
