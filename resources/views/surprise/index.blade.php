@extends('layouts.app')

@section('content')
<div x-data="surpriseApp()" x-init="init()" class="max-w-4xl mx-auto p-4">

  <!-- Rules -->
  <div x-show="showRules" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Aturan Hadiah Sinterklas</h3>
      <p class="mt-2 text-sm">Anda punya <strong>30 detik</strong> untuk memilih salah satu dari 3 kotak. Pilih salah satu — setelah dibuka akan muncul ayat & aksi. Bila tidak memilih, muncul popup gagal.</p>
      <div class="text-right mt-4">
        <button @click="start()" class="px-3 py-2 bg-green-600 text-white rounded">Mulai</button>
      </div>
    </div>
  </div>

  <!-- Fail -->
  <div x-show="showFail" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-80">
      <h3 class="text-xl font-bold text-red-600">Gagal!</h3>
      <p class="mt-2">Waktu habis. Tekan ulang untuk mencoba kembali.</p>
      <div class="mt-4 text-right">
        <button @click="restart()" class="px-3 py-2 bg-blue-600 text-white rounded">Ulang</button>
      </div>
    </div>
  </div>

  <!-- Verse modal -->
  <div x-show="showVerse" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-lg font-bold">Ayat & Aksi</h3>
      <p class="mt-3 text-sm" x-text="currentVerse"></p>
      <div class="mt-3 text-sm italic text-gray-700" x-text="currentAction"></div>
      <div class="mt-4 text-right">
        <button @click="okAfterVerse()" class="px-3 py-2 bg-indigo-600 text-white rounded">OK</button>
      </div>
    </div>
  </div>

  <!-- Finished -->
  <div x-show="showFinished" class="fixed inset-0 bg-black/40 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
      <h3 class="text-xl font-bold">Permainan Selesai</h3>
      <p class="mt-2">Terima kasih sudah bermain!</p>
      <div class="mt-4 text-right">
        <a href="{{ route('dashboard') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Kembali ke Dashboard</a>
      </div>
    </div>
  </div>

  <!-- Main -->
  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
      <div>Waktu: <span x-text="timeLeft"></span>s</div>
      <div class="text-sm text-gray-500">Tahap: <span x-text="round + 1"></span> / <span x-text="rounds"></span></div>
    </div>

    <div class="grid grid-cols-3 gap-6 items-center">
      <template x-for="(box, i) in boxes" :key="i">
        <div class="flex items-center justify-center">
          <button 
            @click="choose(i)" 
            x-text="box.closed ? 'Kotak' : 'Terbuka'" 
            :class="box.closed ? 'w-40 h-40 bg-gray-200 rounded-lg' : 'w-40 h-40 bg-white rounded-lg border flex items-center justify-center p-3 text-sm text-left'" 
            x-html="box.closed ? '<span class=&quot;text-lg&quot;>?</span>' : ('<div><strong>Ayat:</strong><div>'+box.verse+'</div></div>')"
            x-bind:disabled="!allowChoose"
          ></button>
        </div>
      </template>
    </div>

    <div class="mt-6 text-center">
      <button @click="restart()" class="px-3 py-2 bg-blue-600 text-white rounded">Ulangi</button>
    </div>
  </div>
</div>

<script>
function surpriseApp(){
  return {
    // server-provided surprises
    pool: @json($surprises ?? []), // array of {id, verse, action_text}
    rounds: 3,       // jumlah tahap (Anda bisa ubah)
    round: 0,
    boxes: [],       // setiap item: {closed: true, verse:'', action:''}
    timeLeft: 30,
    timerId: null,
    allowChoose: true,
    showRules: true,
    showFail: false,
    showVerse: false,
    showFinished: false,
    currentVerse: '',
    currentAction: '',
    init(){
      // nothing to do until start
    },
    start(){
      if(!this.pool || this.pool.length < 3){
        alert('Data ayat kurang (butuh minimal 3).');
        return;
      }
      this.showRules = false;
      this.round = 0;
      this.showFinished = false;
      this.prepareRound();
    },
    prepareRound(){
      // reset state for the round
      this.allowChoose = true;
      this.timeLeft = 30;
      this.showFail = false;
      this.showVerse = false;
      // pick 3 random unique items from pool
      const shuffled = this.pool.slice().sort(()=>0.5 - Math.random());
      const chosen = shuffled.slice(0,3);
      this.boxes = chosen.map(s => ({ closed: true, verse: s.verse, action: s.action_text, id: s.id }));
      // start timer
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
    choose(index){
      if(!this.allowChoose) return;
      this.allowChoose = false;
      // open selected box
      const b = this.boxes[index];
      b.closed = false;
      this.currentVerse = b.verse;
      this.currentAction = b.action || '';
      // optionally record to server (AJAX)
      // fetch("{{ route('surprise.record') }}", { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ surprise_id: b.id, round: this.round, choice: index }) });
      // show verse modal
      this.showVerse = true;
      clearInterval(this.timerId);
    },
    okAfterVerse(){
      this.showVerse = false;
      // proceed to next round or finish
      this.round++;
      if(this.round >= this.rounds){
        this.showFinished = true;
        return;
      }
      // randomize pool again so next round has different data
      this.prepareRound();
    },
    onTimeout(){
      this.showFail = true;
      this.allowChoose = false;
    },
    restart(){
      // restart whole game
      this.showRules = false;
      this.round = 0;
      this.prepareRound();
    }
  }
}
</script>
@endsection
