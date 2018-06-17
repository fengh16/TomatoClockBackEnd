//index.js
var config = require('../../config')

var wxCharts = require('wxcharts-min.js');
var app = getApp();
var lineChart = null;
var lineChart2 = null;

var startPos = null;
Page({
  data: {
    'month': "",
    'data': {},
    'diff': 0,
    'windowWidth' : 320
  },

  touchHandler: function (e) {
    lineChart.scrollStart(e);
  },
  moveHandler: function (e) {
    lineChart.scroll(e);
  },
  touchEndHandler: function (e) {
    lineChart.scrollEnd(e);
    lineChart.showToolTip(e, {
      format: function (item, category) {
        return category + ' ' + item.name + ':' + item.data
      }
    });
  },
  touchHandler2: function (e) {
    lineChart2.scrollStart(e);
  },
  moveHandler2: function (e) {
    lineChart2.scroll(e);
  },
  touchEndHandler2: function (e) {
    lineChart2.scrollEnd(e);
    lineChart2.showToolTip(e, {
      format: function (item, category) {
        return category + ' ' + item.name + ':' + item.data
      }
    });
  },
  getTomatoTimesData: function () {
    var categories = [];
    var data = [];
    for (var i = 0; i < 31; i++) {
      categories.push((i + 1));
      data.push(Math.random() * (90 - 10) + 10);
    }
    return {
      categories: categories,
      data: data
    }
  },
  getTotalTimeData: function () {
    var categories = [];
    var data = [];
    for (var i = 0; i < 10; i++) {
      categories.push((i + 1));
      data.push(Math.random() * (90 - 10) + 10);
    }
    return {
      categories: categories,
      data: data
    }
  },

  // diff为-1表示当前月份的前一个月
  getMonth: function (diff) {
    // 获取当前日期
    var date = new Date();

    // 获取当前月份
    var nowMonth = date.getMonth() + 1;

    var nowYear = date.getFullYear();

    // 如果是几个月前，就减去几个月
    if (diff < 0) {
      diff = -diff;
      nowYear -= parseInt(diff / 12);
      diff = diff % 12;
      if (nowMonth <= diff) {
        nowYear -= 1;
        nowMonth += (12 - diff);
      }
      else {
        nowMonth -= diff;
      }
    }
    else {
      nowYear += parseInt(diff / 12);
      diff = diff % 12;
      nowMonth += diff;
      if (nowMonth > 12) {
        nowMonth -= 12;
        nowYear += 1;
      }
    }

    // 添加分隔符“-”
    var seperator = "-";

    // 对月份进行处理，1-9月在前面添加一个“0”
    if (nowMonth >= 1 && nowMonth <= 9) {
      nowMonth = "0" + nowMonth;
    }

    // 最后拼接字符串，得到一个格式为(yyyy-MM)的日期
    var nowDate = nowYear + seperator + nowMonth;

    return nowDate;
  },
  minusMonth: function () {
    this.updateInfo(-1);
  },
  addMonth: function () {
    this.updateInfo(1);
  },
  // 参数为+1表示获取下一个月的数据
  updateInfo: function(updateDirection) {
    this.data['diff'] += updateDirection;
    this.data['month'] = this.getMonth(this.data['diff']);
    this.setData({'month': this.data['month']});
    var that = this;
    var temp = {
      'session': wx.getStorageSync('session'),
      'invite': wx.getStorageSync('invite'),
      'month': that.data['month']
      //'month': "2018-06"
    };
    wx.request({
      url: config.service.getUserInfoUrl,
      data: temp,
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        console.log("已查询UserInfo！返回信息：", res.data);
        that.data['data']['data1'] = {
          categories: [],
          data: []
        };
        that.data['data']['data2'] = {
          categories: [],
          data: []
        };
        // 最大天数
        var temp = new Date(parseInt(that.data['month'].split("-")[0]), parseInt(that.data['month'].split("-")[1]),  0);
        var maxdate = temp.getDate();
        var i = 0;
        for (i = 0; i < maxdate; i++) {
          var thisdate = "";
          if (i < 9) {
            thisdate = that.data['month'] + "-0" + (i + 1);
          }
          else {
            thisdate = that.data['month'] + "-" + (i + 1);
          }
          that.data['data']['data1']['data'].push(0);
          that.data['data']['data2']['data'].push(0);
          that.data['data']['data1']['categories'].push(thisdate);
          that.data['data']['data2']['categories'].push(thisdate);
        }
        var simulationData = that.data['data']['data1'];
        var simulationData2 = that.data['data']['data2'];
        for (i in res.data.userInfo) {
          var day = parseInt(res.data.userInfo[i].user_info_date.split("-")[2]);
          that.data['data']['data1']['data'][day - 1] = res.data.userInfo[i].user_info_times;
          that.data['data']['data2']['data'][day - 1] = res.data.userInfo[i].user_info_time;
        }
        lineChart = new wxCharts({
          canvasId: 'lineCanvas',
          type: 'line',
          categories: simulationData.categories,
          animation: false,
          series: [{
            name: 'tomato完成次数',
            data: simulationData.data,
            format: function (val, name) {
              return val + '次';
            }
          }],
          xAxis: {
            disableGrid: false
          },
          yAxis: {
            title: '         todo完成次数',
            format: function (val) {
              return val;
            },
            min: 0
          },
          width: that.data['windowWidth'],
          height: 200,
          dataLabel: true,
          dataPointShape: true,
          enableScroll: true,
          extra: {
            lineStyle: 'curve'
          }
        });

        lineChart2 = new wxCharts({
          canvasId: 'lineCanvas2',
          type: 'line',
          categories: simulationData2.categories,
          animation: false,
          series: [{
            name: '工作时间',
            data: simulationData2.data,
            format: function (val, name) {
              return val + '分钟';
            }
          }],
          xAxis: {
            disableGrid: false
          },
          yAxis: {
            title: '           工作时长 / 分',
            format: function (val) {
              return val;
            },
            min: 0
          },
          width: that.data['windowWidth'],
          height: 200,
          dataLabel: true,
          dataPointShape: true,
          enableScroll: true,
          extra: {
            lineStyle: 'curve'
          }
        });
      }
    })
    
  },
  onLoad: function (e) {
    this.data['diff'] = 0;
    try {
      var res = wx.getSystemInfoSync();
      this.data['windowWidth'] = res.windowWidth;
    } catch (e) {
      console.error('getSystemInfoSync failed!');
    }
    this.updateInfo(0);
  }
});