@extends('layouts.app')

@section('sidebar')
  <a href="{{ route('guess.menu') }}" class="block py-2">Back</a>
@endsection

@section('content')
<div x-data="guessDuo()" x-init="init()" class="max-w-4xl mx-auto p-4">
  <!-- Rules modal -->
  <div x-show="showRules" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Aturan Tebak Gambar (Duo)</h3>
      <p class="mt-2 text-sm">Pilih warna (Biru atau Merah) lalu mulai. Bergantian menjawab. Setiap jawaban benar menambah progress di sisi pemain.</p>

      <div class="mt-4">
        <label class="inline-flex items-center mr-4">
          <input type="radio" name="playerColor" value="blue" x-model="playerColor" checked>
          <span class="ml-2">Biru</span>
        </label>
        <label class="inline-flex items-center">
          <input type="radio" name="playerColor" value="red" x-model="playerColor">
          <span class="ml-2">Merah</span>
        </label>
      </div>

      <div class="text-right mt-4">
        <button @click="start()" class="px-3 py-2 bg-green-600 text-white rounded">Mulai</button>
      </div>
    </div>
  </div>

  <!-- Fail modal -->
  <div x-show="showFail" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-80">
      <h3 class="text-xl font-bold text-red-600">Gagal!</h3>
      <p class="mt-2">Waktu habis. Jawaban dianggap kosong. Lanjut ke pemain berikutnya.</p>
      <div class="mt-4 text-right">
        <button @click="continueAfterFail()" class="px-3 py-2 bg-blue-600 text-white rounded">Lanjut</button>
      </div>
    </div>
  </div>

  <!-- Summary modal -->
  <div x-show="showSummary" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Ringkasan Duo</h3>
      <p class="mt-2">Skor Biru: <strong x-text="score.blue"></strong></p>
      <p class="mt-1">Skor Merah: <strong x-text="score.red"></strong></p>
      <div class="mt-4 text-right">
        <a href="{{ route('guess.menu') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Kembali</a>
      </div>
    </div>
  </div>

  <!-- Main -->
  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
      <div class="text-sm font-semibold">Waktu: <span 
        x-text="timeLeft"
        :class="timeLeft <= 5 ? 'text-red-600 font-bold' : 'text-black'"
    ></span> detik</div>
      <div class="w-full max-w-xl">
        <div class="relative h-6 bg-gray-200 rounded">
          <!-- blue fill from left -->
          <div :style="`width:${bluePercent}%`" class="absolute left-0 top-0 bottom-0 bg-blue-600"></div>
          <!-- red fill from right -->
          <div :style="`width:${redPercent}%`" class="absolute right-0 top-0 bottom-0 bg-red-600"></div>
          <div class="absolute inset-0 flex items-center justify-center text-sm text-white font-bold">
            <span x-text="scoreText"></span>
          </div>
        </div>
      </div>
    </div>

    <div :class="activePlayer === 'blue' ? 'bg-blue-50 p-4 rounded' : 'bg-red-50 p-4 rounded'">
      <div class="flex justify-center mb-4">
        <img 
    :src="current.image_path ? '/storage/' + current.image_path : '/images/placeholder.png'" 
    class="max-h-[420px] w-auto object-contain mx-auto rounded-lg shadow">
      </div>

      <div class="mb-4 text-center">
        <div x-text="current ? current.prompt || '' : ''"></div>
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
        <button @click="submit()" class="px-4 py-2 bg-indigo-600 text-white rounded">Submit</button>
      </div>
    </div>
  </div>
</div>

<script>
function guessDuo(){
  return {
    questions: @json($questions ?? []),
    index: 0,
    current: null,
    playerColor: 'blue',
    activePlayer: 'blue', // who plays this round
    timeLeft: 16,
    timerId: null,
    showRules: true,
    showFail: false,
    showSummary: false,
    slots: [],
    score: {blue:0, red:0},
    highlightClass: '',


    init(){
      if(this.questions.length) this.loadCurrent();
    },

    start(){
      this.showRules = false;
      this.activePlayer = this.playerColor;
      this.loadCurrent();
      this.startTimer();
    },

    loadCurrent(){
        this.highlightClass = '';
      this.current = this.questions[this.index];
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
      const el = event.target;
      if(el.value && i < this.slots.length-1){
        el.nextElementSibling?.focus();
      }
    },

    submit(){
    clearInterval(this.timerId);

    const answer = this.slots.join('').trim();

    const payload = { 
        question_id: this.current.id, 
        answer: answer, 
        player: this.activePlayer, 
        time_taken_seconds: (this.current.time_limit_seconds ?? 16) - this.timeLeft 
    };

    fetch("{{ route('guess.duo.answer') }}", {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {

        const correct = !!data.correct;

        // ⭐ Highlight sesuai jawaban
        if(correct){
            this.highlightClass = 'border-2 border-green-500 bg-green-50';
            this.score[this.activePlayer] += 1;  // tambah skor pemain
        } else {
            this.highlightClass = 'border-2 border-red-500 bg-red-50';
        }

        // ⭐ Tunggu sebentar agar pemain melihat highlight
        setTimeout(() => {
            this.afterRound();
        }, 700);

    })
    .catch(() => {
        // Jika error server → anggap salah
        this.highlightClass = 'border-2 border-red-500 bg-red-50';

        setTimeout(() => {
            this.afterRound();
        }, 700);
    });
},
    onTimeout(){
      // treat as miss
      this.showFail = true;
    },

    continueAfterFail(){
      this.showFail = false;
      this.afterRound();
    },

    afterRound(){
      // switch active player and go next
      this.index++;
      // switch player
      this.activePlayer = this.activePlayer === 'blue' ? 'red' : 'blue';

      if(this.index >= this.questions.length){
        this.showSummary = true;
        return;
      }

      this.loadCurrent();
      this.startTimer();
    },

    get bluePercent(){
      const total = this.score.blue + this.score.red;
      if(total === 0) return 0;
      return Math.min(100, Math.round((this.score.blue / total) * 100));
    },

    get redPercent(){
      const total = this.score.blue + this.score.red;
      if(total === 0) return 0;
      return Math.min(100, Math.round((this.score.red / total) * 100));
    },

    get scoreText(){
      return `${this.score.blue} - ${this.score.red}`;
    }
  }
}
</script>
@endsection
