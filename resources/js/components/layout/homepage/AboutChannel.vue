<template>
    <div id="accordion" class="about-channel overflow-auto">
        <div class="p-3">
            <strong>About <span>{{ channelDetail.channelName }}</span></strong>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#channeldetail" aria-expanded="false" aria-controls="collapseTwo">
                        <span class="fas fa-info mx-2"></span>Channel Detail
                    </button>
                </h5>
            </div>
            <div id="channeldetail" class="collapse" data-parent="#accordion">
                <div class="card-body">
                    {{ channelDetail.channelPurpose }}
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#pinned" aria-expanded="false" aria-controls="collapseTwo">
                        <span class="fas fa-thumbtack mx-2"></span>Pinned
                    </button>
                </h5>
            </div>
            <div id="pinned" class="collapse padding-5" data-parent="#accordion">
                <div class="pin-item cursor-pointer comment"
                     @click.prevent="showPinItem(item.id)"
                     v-for="(item, index) in channelDetail.listPinItems"
                     :key="item.id + `---` + index"
                >
                    <div class="v-comment-info">
                        <div class="left">
                            <router-link :to="'/' + '@' + item.creator.data.name"
                                         class="avatar user-select">
                                <img v-bind:src="item.creator.data.avatar" class="about-channel-avatar">
                            </router-link>
                            <span >{{ item.creator.data.name }}</span>
                            <el-tooltip :content="'Created: ' + date(item.created_at.date)"
                                        class="float-right margin-left-15"
                                        placement="top"
                                        transition="false"
                                        :open-delay="500">
                                <a class="date margin-right-1"
                                   @click.prevent="openOrigin">
                                    {{ date(item.created_at.date) }}
                                </a>
                            </el-tooltip>
                        </div>
                    </div>
                    <div class="text ml-5">
                        <markdown :text="item.content"></markdown>

                    </div>

                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#memberlist" aria-expanded="true" aria-controls="collapseOne">
                        <span class="fas fa-user mx-2"></span>Member
                    </button>
                </h5>
            </div>

            <div id="memberlist" class="collapse" data-parent="#accordion">
                <div class="card-body">
                    <div class="list-group">
                        <li
                                class="list-group-item list-group-item-action cursor-pointer"
                                v-for="(user, index) in channelDetail.listUsers" :key="index">
                            <span class="dropdown float-right">
                                <a href="#" role="button"data-toggle="dropdown" class="text-dark" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <span class="dropdown-item" @click.prevent="showProfile(user)">View Profile</span>
                                    <span class="dropdown-item" v-if="currentUser.id != user.id" @click="directMess(user)">Direct Message</span>
                                    <span class="dropdown-item" v-show="currentUser.id != user.id" @click="removeUser">Remove User</span>
                                </div>
                            </span>
                            <img :src="user.avatar" class="rounded-circle mr-2" style="width: 30px;height:30px" alt="profile-img">
                            {{user.name}}
                        </li>
                    </div>

                    <a class="dropdown-item mt-3" data-toggle="modal" data-target="#inviteModal">
                        <p>
                            <span class="fas fa-hand-holding-heart mr-2"></span>
                            Invite
                        </p>
                    </a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#files" aria-expanded="false" aria-controls="collapseTwo">
                        <span class="fas fa-file mx-2"></span>Files
                    </button>
                </h5>
            </div>
            <div id="files" class="collapse" data-parent="#accordion">
                <div class="card-body scroll-list-file">
                    <ul class="list-group">
                        <li class="list-group-item" v-for="(file, index) in channelDetail.listFile">
                            <span class="dropdown float-right">
                                <a href="#" role="button"data-toggle="dropdown" class="text-dark" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" @click="copy(file.file_path)">Copy link</a>
                                    <a class="dropdown-item" @click="download(file)">Download</a>
                                </div>
                            </span>
                            <i class="far fa-file mr-2"></i>
                            <span class="cursor-pointer link-color" @click="download(file)">
                                {{file.file_name}}
                            </span>
                            <p class="text-muted">
                                <strong class="username">{{file.creator_name}}</strong>
                                <small class="timeOfFile" v-if="file.created_at">{{date(file.created_at.date)}}</small>
                            </p>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
  import Markdown from '../../includes/Markdown.vue';
  import {get, post} from '../../../helper/request.js'
    export default {
      props: ['channelDetail'],

      data() {
        return {
          currentUser: this.$store.state.auth.user
        }
      },

      components: {
        Markdown
      },

      computed: {

      },

      mounted() {
        $('#channeldetail').collapse('toggle');
      },

      methods:{
        date(time) {
          return moment(time)
            .fromNow(true);
        },

        directMess(user) {
          let type = 2;
          let invited_users = [];
          invited_users.push(user.id);
          let name = this.currentUser.name + ', ' + user.name;
          let payload = {
            type : type,
            purpose: 'Direct Messages between ' + name,
            description: '',
            name: name,
            invited_users: invited_users
          }
          let url = '/api/channel/create'
          post(url, payload).then((res) => {
            console.log('res: ', res);
            if(res.data.data) {
              this.$router.push({
                name: 'ChannelDetail',
                params: {
                  id: res.data.data.channel_id
                }
              })
              this.$eventHub.$emit('newDirectMessage', res.data.data);
            }
          }).catch((err) => {
            console.log('err: ', err);
            this.$message({
              type: 'error',
              message: 'Something error: ', err
            })
          })
        },

        showProfile(user){
          this.$emit('showProfile', user);
        },
        removeUser(){
          this.$emit('removeUser');
        },

        date(date) {
          return moment(date)
            .fromNow(true);
        },

        showPinItem(postId) {
          this.$eventHub.$emit('showPinItem', postId);
        }
      }
    }

</script>

<style>
    .padding-5 {
        padding: 5px;
    }
    .pin-item {
        overflow: hidden;
        border-radius: 4px;
        padding: 12px;
        margin: 5px 0;
        height: 120px;
        box-sizing: border-box;
        font-size: 14px;
        background-color: #b8c2cc;
        opacity: 0.8;
    }
    .background-pinned {
        background: #f3d19d !important;
    }

    .scroll-list-file {
        max-height: 300px;
        overflow: auto;
    }
</style>
