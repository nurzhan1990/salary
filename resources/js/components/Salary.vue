<template>
<div>
    <form>
        <div class="form-group">
            <label for="otr_dney">Отработанное количество дней</label>
            <VueDatePicker v-model="date" type="month"/>
        </div>

        <div class="form-group">
            <label for="salary">Оклад в тенге</label>
            <input type="number" class="form-control" id="salary"  name="salary" v-model="salary.oklad">
        </div>

        <div class="form-group">
            <label for="salary">Норма дней в месяц</label>
            <input type="number" class="form-control" id="month_work_days"  name="month_work_days" v-model="salary.month_work_days" disabled>
        </div>

        <div class="form-group">
            <label for="otr_dney">Отработанное количество дней</label>
            <input type="number" class="form-control" id="otr_dney"  name="otr_dney" v-model="salary.work_days">
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="nalog" v-model="salary.nalog">
                <label class="form-check-label" for="nalog">
                    Имеется ли налоговый вычет 1 МЗП
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="pensioner" v-model="salary.pensioner">
                <label class="form-check-label" for="pensioner">
                    Является ли сотрудник пенсионером
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="disabled_person" v-model="salary.disabled_person">
                <label class="form-check-label" for="disabled_person">
                    Является ли сотрудник инвалидом
                </label>
            </div>
        </div>

        <div class="form-group" v-if="salary.disabled_person">
            <label for="disabled_person_group">Группа</label>
            <select class="form-control" id="disabled_person_group" v-model="salary.disabled_person_group">
                <option v-bind:value="1">1</option>
                <option v-bind:value="2">2</option>
                <option v-bind:value="3">3</option>
            </select>
        </div>

    </form>

    <button type="button" class="btn btn-primary" v-on:click="Calculate()">Post</button>

</div>
</template>

<script>
import business from 'moment-business';
import moment from 'moment';

import VueDatePicker from '@mathieustan/vue-datepicker';
import '@mathieustan/vue-datepicker/dist/vue-datepicker.min.css';

Vue.use(VueDatePicker, {
    lang: 'ru'
});
export default {
    data() {
        return {
            date: null,
            loadState: false,
            salary:{
                date: null,
                oklad: null,
                otr_dny: null,
                month_work_days: 0,
                work_days: null,
                nalog: null,
                pensioner: null,
                disabled_person: null,
                disabled_person_category: null,
                disabled_person_group: 1
            }
        }
    },
    mounted(){
    },
    methods: {
        Calculate(){
            this.loadState = true
            axios.post('/salary/Calculate', {
                salary: this.salary
            }).then(response => {
                this.loadState = false;
                console.log(response)
            }).catch(error => {
                this.loadState = false;
            })
        },
    },
    watch: {
        date: function () {
            var dateStart = moment(this.date).startOf('month').format('YYYY-MM-DD');
            var dateEnd = moment(this.date).endOf('month').format('YYYY-MM-DD');

            var day = moment(dateStart);
            var businessDays = 0;

            while (day.isSameOrBefore(dateEnd,'day')) {
                if (day.day()!=0 && day.day()!=6) businessDays++;
                day.add(1,'d');
            }

            this.salary.date = this.date
            this.salary.month_work_days = businessDays;
        },
    }
}
</script>

<style scoped>

</style>
