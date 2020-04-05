<template>
  <ul class="c-Keywords m-Cards-Wrapper2 u-scroll">


    <li v-for="item in keyword_cmp" class="c-Keywords__Inner" v-bind:key="item.id">
      <div class="u-container u-card-back">

        <div class="c-Keywords-Inner u-txt-l">
          <div class="c-Chip-Wrapper" v-for="(key_pare, index) in item.keyword" v-bind:key="key_pare.id">

            <p v-if="index != 0" class="a-Chip">
              <span>{{key_pare.opt}}</span>
            </p>
            <p class="a-Chip is-pink">
              <span>{{key_pare.txt}}</span>
            </p>
          </div>
        </div>          
      </div>
    </li>
  
          
	</ul>
</template>

<script>
//「自動フォロー」用のキーワードを表示します。
export default {
  name: "keyword_cards",
  props: {

  },
  

  computed: {
    keyword_cmp: function () {
      return this.keyword_data;
    }
  },

}
</script>
<style lang="scss" scoped>

</style>