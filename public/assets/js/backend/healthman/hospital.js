define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'healthman/hospital/index' + location.search,
                    add_url: 'healthman/hospital/add',
                    edit_url: 'healthman/hospital/edit',
                    del_url: 'healthman/hospital/del',
                    multi_url: 'healthman/hospital/multi',
                    table: 'hospital',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'flagship', title: __('Flagship'), searchList: {"0":__('Flagship 0'),"1":__('Flagship 1'),"2":__('Flagship 2')}, formatter: Table.api.formatter.normal},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('相关事项'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'detail',
                                    text: __('相关事项'),
                                    title: __('添加量级轨迹'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'healthman/hostrace/add',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'lat', title: __('Lat'), operate:'BETWEEN', visible:false},
                        {field: 'lng', title: __('Lng'), operate:'BETWEEN', visible:false},
                        {field: 'name', title: __('Name')},
                        {field: 'location', title: __('Location')},
                        {field: 'poid', title: __('Poid')},
                        {field: 'title', title: __('Title')},
                        {field: 'address', title: __('Address')},
                        {field: 'tel', title: __('Tel')},
                        {field: 'category', title: __('Category')},
                        {field: 'adcode', title: __('Adcode')},
                        {field: 'province', title: __('Province')},
                        {field: 'city', title: __('City')},
                        {field: 'district', title: __('District')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'healthman/hospital/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'healthman/hospital/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'healthman/hospital/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});