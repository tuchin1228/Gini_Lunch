<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GINI LUNCH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>


<body>

    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
        }

        table tr th {
            text-align: center;
            font-weight: 500;
            color: rgb(112, 47, 17);
            font-size: 1.1rem;
            background: rgb(255, 250, 223) !important;
        }

        table tr td {
            text-align: center;
        }

        @media all and (max-width:550px) {
            table tr td {
                font-size: 0.9rem;
            }
        }

    </style>

    <div id="app" class="container my-2">
        <div class="border p-2">
            <table class="table  table-bordered table-hover">
                <tr>
                    <th width="20%">姓名</th>
                    <th width="20%">餘額</th>
                    <th width="20%">項目</th>
                    <th width="20%">花費</th>
                    <th width="20%">儲值</th>
                </tr>
                <tr v-for="employee in employees" :key="employee.id">
                    <td>@{{employee.name}}</td>
                    <td>@{{employee.total}}</td>
                    <td><input class="form-control" type="text" name="" v-model="employee.title" id=""></td>
                    <td><input class="form-control" type="number" name="" v-model="employee.subtotal" id=""></td>
                    <td><input class="form-control" type="number" name="" v-model="employee.store" id=""></td>
                </tr>
            </table>
            <div>
                <button @click="SubmitLunch" class="btn btn-primary " style="width:100%;">送出</button>
            </div>
        </div>
        <div class="border p-2 mt-2">
            <div style="margin: 3vh 0" class="row align-item-center">
                <div class="col-12 col-sm-6  row m-0 p-0">
                    <input type="date" class="form-control my-1  col-12 col-md-6 " name="" id="" v-model="start">
                    <input type="date" class="form-control my-1  col-12 col-md-6 " name="" id="" v-model="end">
                </div>
                <div class="col-12 col-sm-6 p-0">
                    <button class="btn btn-primary m-1 " @click="GetHistory(0)">全部查詢</button>
                    <button class="btn btn-success m-1 " @click="GetHistory(1)">儲值</button>
                    <button class="btn btn-warning m-1 " @click="GetHistory(2)">消費</button>
                </div>
            </div>
            <table class="table  table-bordered table-hover">
                <tr>
                    <th width="25%">姓名</th>
                    <th width="25%">項目</th>
                    <th width="25%">金額</th>
                    <th width="25%">日期</th>
                </tr>
                <tr v-for="item in historyDetail" :key="item.id" :style="[item.type == 0 ? {'background':'#ffc8c8'} :
                    {'background':'#d6f8da'}]">
                    <td>@{{item.name}}</td>
                    <td>@{{item.title}}</td>
                    <td><span v-if="item.type == 0">-</span>@{{item.value}}</td>
                    <td>@{{item.created_at}}</td>
                </tr>
            </table>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.24.0/axios.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                employees: [],
                start: `${new Date().getFullYear()}-${new Date().getMonth()+1}-${new Date().getDate() < 10 ? '0': '' }${new Date().getDate()}`,
                end: `${new Date().getFullYear()}-${new Date().getMonth()+1}-${new Date().getDate()+1 < 10 ? '0': '' }${new Date().getDate()+1}`,
                historyDetail: []
            },
            created() {
                this.GetEmployee()
            },
            methods: {
                async GetHistory(searchType) {
                    const vm = this;
                    if (!vm.start || !vm.end) {
                        alert('日期全都要填')
                        return null;
                    }
                    let res = await axios.post('/api/getHistory', {
                        start: vm.start,
                        end: vm.end,
                        searchType: searchType
                    })
                    console.log(res);
                    this.historyDetail = res.data
                },
                async GetEmployee() {
                    let res = await axios.get('/api/getEmployee')
                    res.data.forEach(item => {
                        item.title = ''
                        item.subtotal = ''
                        item.store = ''
                    });
                    this.employees = res.data
                    console.log(res);
                },
                async SubmitLunch() {
                    const vm = this;
                    let error = false
                    vm.employees.forEach(item => {
                        if (parseInt(item.subtotal, 10) < 0 || parseInt(item.store, 10) < 0) {
                            alert('儲值或花費不得小於零')
                            // return null;
                            error = true
                        }
                    })
                    if (error) {
                        return null;
                    }
                    let res = await axios.post('/api/insertLunch', {
                        employees: vm.employees
                    })
                    console.log(res);
                    if (res.data.success) {
                        vm.GetEmployee()
                    }

                }
            }
        })

    </script>



</body>

</html>
